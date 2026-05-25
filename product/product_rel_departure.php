<?php
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim    = isset($_GET['data_fim'])    ? $_GET['data_fim']    : '';

$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

$sql = "SELECT s.id_saida,
            emp.nome AS empresa,
            p.nome AS produto,
            s.valor_unitario,
            setr.nome AS setor,
            s.responsavel,
            s.quantidade,
            s.tipo_custo,
            s.data_saida,
            s.numero_ticket,
            c.nome AS cidade,
            e.nome AS estado,
            s.observacao
        FROM saida_produto s
        JOIN empresas emp  ON s.id_empresa = emp.id
        JOIN produtos p    ON s.id_produto = p.id
        JOIN setores setr  ON s.id_setor   = setr.id
        JOIN cidades c     ON s.id_cidade  = c.id
        JOIN estados e     ON s.id_estado  = e.id
        WHERE 1=1";

$params = [];
$types  = "";

if (!empty($data_inicio) && !empty($data_fim)) {
    $sql   .= " AND s.data_saida BETWEEN ? AND ?";
    $params[] = $data_inicio;
    $params[] = $data_fim;
    $types   .= "ss";
}

$sql   .= " ORDER BY s.data_saida DESC, s.id_saida ASC LIMIT ? OFFSET ?";
$params[] = $registros_por_pagina;
$params[] = $offset;
$types   .= "ii";

$stmt = $mysqli->prepare($sql);
if ($stmt === false) die("Erro na preparação da consulta: " . $mysqli->error);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$sql_total    = "SELECT COUNT(*) AS total FROM saida_produto s WHERE 1=1";
$params_total = [];
$types_total  = "";
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql_total     .= " AND s.data_saida BETWEEN ? AND ?";
    $params_total[] = $data_inicio;
    $params_total[] = $data_fim;
    $types_total   .= "ss";
}
$stmt_total = $mysqli->prepare($sql_total);
if ($stmt_total === false) die("Erro na preparação da consulta de contagem: " . $mysqli->error);
if (!empty($params_total)) $stmt_total->bind_param($types_total, ...$params_total);
$stmt_total->execute();
$total_registros = $stmt_total->get_result()->fetch_assoc()['total'];
$total_paginas   = max(1, ceil($total_registros / $registros_por_pagina));

$sql_total_mes = "SELECT DATE_FORMAT(s.data_saida, '%Y-%m') AS mes,
                         SUM(s.quantidade * s.valor_unitario) AS total_mes
                  FROM saida_produto s WHERE 1=1";
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql_total_mes .= " AND s.data_saida BETWEEN ? AND ?";
}
$sql_total_mes .= " GROUP BY DATE_FORMAT(s.data_saida, '%Y-%m') ORDER BY mes DESC";

$stmt_total_mes = $mysqli->prepare($sql_total_mes);
if ($stmt_total_mes === false) die("Erro na preparação da consulta de total por mês: " . $mysqli->error);
if (!empty($data_inicio) && !empty($data_fim)) {
    $stmt_total_mes->bind_param("ss", $data_inicio, $data_fim);
}
$stmt_total_mes->execute();
$result_total_mes = $stmt_total_mes->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Saídas</title>
    <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* ── Table Card ──────────────────────────────────────────── */
        .table-card {
            background: var(--paper); border: 1px solid var(--border);
            border-radius: var(--radius-lg); overflow: hidden;
            margin-bottom: 32px;
        }
        .table-card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 24px; border-bottom: 1px solid var(--border);
        }
        .table-card-title { font-size: 13px; font-weight: 500; color: var(--ink); }
        .table-card-meta  { font-size: 12px; color: var(--ink-4); font-family: var(--font-mono); }

        .table-card table { width: 100% !important; border-collapse: collapse; font-size: 13.5px; }
        .table-card thead th {
            padding: 11px 16px; text-align: left;
            font-size: 10.5px; letter-spacing: .10em; text-transform: uppercase;
            font-weight: 500; color: var(--ink-3); font-family: var(--font-mono);
            background: var(--paper-2); border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .table-card thead th:first-child { padding-left: 24px; }
        .table-card thead th:last-child  { padding-right: 24px; }
        .table-card tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
        .table-card tbody tr:last-child  { border-bottom: none; }
        .table-card tbody tr:hover       { background: var(--paper-2); }
        .table-card tbody td { padding: 13px 16px; color: var(--ink-2); vertical-align: middle; }
        .table-card tbody td:first-child { padding-left: 24px; color: var(--ink); font-weight: 500; }
        .table-card tbody td:last-child  { padding-right: 24px; }
        .td-mono  { font-family: var(--font-mono); font-size: 12.5px; }
        .td-right { text-align: right; }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            font-family: var(--font-body); font-size: 13px;
            color: var(--ink-3); margin-bottom: 14px;
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 7px 11px; border: 1px solid var(--border-strong);
            border-radius: var(--radius-sm); color: var(--ink);
            font-family: var(--font-body); font-size: 13px;
            outline: none; background: var(--paper); margin-left: 6px;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--accent); box-shadow: 0 0 0 3px rgba(50,104,228,.12);
        }

        .dataTables_wrapper .dataTables_length select {
            padding: 6px 10px; border: 1px solid var(--border-strong);
            border-radius: var(--radius-sm); background: var(--paper);
            color: var(--ink); font-family: var(--font-body); font-size: 13px;
            outline: none; margin: 0 4px;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 12.5px; color: var(--ink-4); padding-top: 10px;
        }

        .dataTables_wrapper .dataTables_paginate { padding-top: 10px; text-align: right; }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 32px; height: 32px; padding: 0 8px;
            border-radius: var(--radius-sm); font-family: var(--font-mono); font-size: 12.5px;
            background: var(--paper); border: 1px solid var(--border) !important;
            color: var(--ink-3) !important; cursor: pointer; transition: all .13s; margin: 0 2px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--paper-2) !important; color: var(--ink) !important;
            border-color: var(--border-strong) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--accent) !important; border-color: var(--accent) !important;
            color: #fff !important; font-weight: 500;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            opacity: .3; pointer-events: none;
        }

        /* ── Filter bar ─────────────────────────────────────── */
        .filter-bar {
            background: var(--paper-2); border: 1px solid var(--border);
            border-radius: var(--radius-lg); padding: 20px 22px;
            margin-bottom: 24px;
        }
        .filter-bar-title {
            font-family: var(--font-mono); font-size: 10.5px; font-weight: 500;
            letter-spacing: .12em; text-transform: uppercase; color: var(--ink-4);
            margin-bottom: 16px; display: block;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
            gap: 14px; align-items: end;
        }
        .filter-actions { display: flex; gap: 8px; align-items: flex-end; }

        /* ── Active filter badges ──────────────────────────── */
        .active-filters { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
        .filter-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 3px 10px; border-radius: 99px;
            font-size: 12px; font-family: var(--font-mono);
            background: var(--paper-2); border: 1px solid var(--border);
            color: var(--ink-3);
        }
        .filter-badge button {
            background: none; border: none; cursor: pointer;
            color: var(--ink-4); font-size: 11px; padding: 0; line-height: 1;
            transition: color .13s;
        }
        .filter-badge button:hover { color: var(--danger); }

        /* ── Chart container ───────────────────────────────── */
        .chart-section { display: grid; grid-template-columns: 1fr auto; gap: 24px; align-items: start; }
        .chart-wrap { min-width: 0; }
        .monthly-table table.data-table { font-size: 12.5px; }

        @media (max-width: 900px) {
            .chart-section { grid-template-columns: 1fr; }
            .page-header { flex-wrap: wrap; gap: 12px; }
            .page-header-actions { width: 100%; display: flex; gap: 8px; }
            .page-header-actions .btn { flex: 1; justify-content: center; }
            .filter-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 600px) {
            .filter-grid { grid-template-columns: 1fr; }
            .filter-actions { flex-direction: column; }
            .filter-actions .btn { width: 100%; justify-content: center; }
            .table-card-header { flex-direction: column; align-items: flex-start; gap: 4px; }
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length { display: block; width: 100%; margin-bottom: 8px; }
            .dataTables_wrapper .dataTables_filter input { width: 100%; margin: 6px 0 0 0; box-sizing: border-box; }
            .dataTables_wrapper .dataTables_paginate { text-align: center; }
        }
    </style>
</head>
<body>
<div class="mobile-bar">
    <button class="hamburger" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <span style="font-size:14px;font-weight:500">Rel. Saídas</span>
    <span></span>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main">
        <header class="topbar">
            <nav class="topbar-breadcrumb">
                <a href="/centro_de_custos/dashboard/painel.php">Início</a>
                <span>/</span>
                <span>Produtos</span>
                <span>/</span>
                <span class="current">Rel. Saídas</span>
            </nav>
            <div class="topbar-right">
                <span class="topbar-username"><?= htmlspecialchars($primeiro_nome, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="topbar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </header>

        <div class="content">
            <div class="page-header">
                <div>
                    <span class="page-eyebrow">Relatório</span>
                    <h1 class="page-title"><strong>Relatório de</strong> Saídas</h1>
                </div>
                <div class="page-header-actions">
                    <a id="exportLink" href="export_departure.php<?php
                        $qs = http_build_query(array_filter([
                            'data_inicio' => $data_inicio,
                            'data_fim'    => $data_fim,
                            'empresa'     => $_GET['empresa']     ?? '',
                            'produto'     => $_GET['produto']     ?? '',
                            'tipo_custo'  => $_GET['tipo_custo']  ?? '',
                            'setor'       => $_GET['setor']       ?? '',
                            'responsavel' => $_GET['responsavel'] ?? '',
                        ]));
                        echo $qs ? '?' . $qs : '';
                    ?>" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Exportar
                    </a>
                    <button class="btn btn-secondary" onclick="document.getElementById('helpModal').classList.add('open')">
                        <i class="fas fa-question-circle"></i> Ajuda
                    </button>
                </div>
            </div>

            <!-- Filtros ativos -->
            <?php if (!empty($data_inicio) || !empty($data_fim) || !empty($_GET['empresa']) || !empty($_GET['produto']) || !empty($_GET['setor']) || !empty($_GET['responsavel'])): ?>
            <div class="active-filters">
                <?php if (!empty($data_inicio) && !empty($data_fim)): ?>
                    <span class="filter-badge">
                        Data: <?= date('d/m/Y', strtotime($data_inicio)) ?> — <?= date('d/m/Y', strtotime($data_fim)) ?>
                        <button onclick="removeFilter('date')" title="Remover">✕</button>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['empresa'])): ?>
                    <span class="filter-badge">
                        Empresa: <?= htmlspecialchars($_GET['empresa']) ?>
                        <button onclick="removeFilter('empresa')" title="Remover">✕</button>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['produto'])): ?>
                    <span class="filter-badge">
                        Produto: <?= htmlspecialchars($_GET['produto']) ?>
                        <button onclick="removeFilter('produto')" title="Remover">✕</button>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['setor'])): ?>
                    <span class="filter-badge">
                        Setor: <?= htmlspecialchars($_GET['setor']) ?>
                        <button onclick="removeFilter('setor')" title="Remover">✕</button>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['responsavel'])): ?>
                    <span class="filter-badge">
                        Responsável: <?= htmlspecialchars($_GET['responsavel']) ?>
                        <button onclick="removeFilter('responsavel')" title="Remover">✕</button>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="filter-bar">
                <span class="filter-bar-title">Filtros</span>
                <form method="GET" action="" id="filterForm">
                    <div class="filter-grid">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Data Início</label>
                            <input type="date" id="data_inicio" name="data_inicio" class="form-control flatpickr" value="<?= htmlspecialchars($data_inicio) ?>">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Data Fim</label>
                            <input type="date" id="data_fim" name="data_fim" class="form-control flatpickr" value="<?= htmlspecialchars($data_fim) ?>">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Empresa</label>
                            <input type="text" id="empresa" name="empresa" class="form-control" placeholder="Nome da empresa" value="<?= htmlspecialchars($_GET['empresa'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Produto</label>
                            <input type="text" id="produto" name="produto" class="form-control" placeholder="Nome do produto" value="<?= htmlspecialchars($_GET['produto'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Tipo de Custo</label>
                            <select id="tipo_custo" name="tipo_custo" class="form-control">
                                <option value="">Todos</option>
                                <option value="MANUTENCAO" <?= ($_GET['tipo_custo'] ?? '') === 'MANUTENCAO' ? 'selected' : '' ?>>Manutenção</option>
                                <option value="INVESTIMENTO" <?= ($_GET['tipo_custo'] ?? '') === 'INVESTIMENTO' ? 'selected' : '' ?>>Investimento</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Setor</label>
                            <input type="text" id="setor" name="setor" class="form-control" placeholder="Nome do setor" value="<?= htmlspecialchars($_GET['setor'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Responsável</label>
                            <input type="text" id="responsavel" name="responsavel" class="form-control" placeholder="Responsável" value="<?= htmlspecialchars($_GET['responsavel'] ?? '') ?>">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="?" class="btn btn-secondary">Limpar</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabela de registros -->
            <div class="table-card">
                <div class="table-card-header">
                    <span class="table-card-title">
                        <i class="fas fa-table"></i> Registros de Saída
                    </span>
                    <span class="table-card-meta"><?= $total_registros ?> registro(s)</span>
                </div>
                <div style="overflow-x:auto;">
                        <table id="dataTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Produto</th>
                                    <th>Valor Unit.</th>
                                    <th>Setor</th>
                                    <th>Responsável</th>
                                    <th>Quantidade</th>
                                    <th>Tipo de Custo</th>
                                    <th>Data Saída</th>
                                    <th>Nº Ticket</th>
                                    <th>Cidade</th>
                                    <th>Estado</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result->data_seek(0);
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['empresa']) ?></td>
                                        <td><?= htmlspecialchars($row['produto']) ?></td>
                                        <td class="td-mono" data-order="<?= $row['valor_unitario'] ?>">R$&nbsp;<?= number_format($row['valor_unitario'], 2, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($row['setor']) ?></td>
                                        <td><?= htmlspecialchars($row['responsavel']) ?></td>
                                        <td class="td-mono td-right" data-order="<?= $row['quantidade'] ?>"><?= number_format($row['quantidade'], 0, ',', '.') ?></td>
                                        <td class="td-mono"><?= htmlspecialchars($row['tipo_custo']) ?></td>
                                        <td class="td-mono" data-order="<?= strtotime($row['data_saida']) ?>"><?= date('d/m/Y', strtotime($row['data_saida'])) ?></td>
                                        <td class="td-mono"><?= htmlspecialchars($row['numero_ticket']) ?></td>
                                        <td><?= htmlspecialchars($row['cidade']) ?></td>
                                        <td><?= htmlspecialchars($row['estado']) ?></td>
                                        <td><?= nl2br(htmlspecialchars($row['observacao'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                </div>
            </div>

            <!-- Gráfico por mês -->
            <?php if ($result_total_mes->num_rows > 0): ?>
            <div class="card">
                <div class="card-header">
                    <span class="card-header-title">
                        <i class="fas fa-chart-bar"></i> Valor Total de Saídas por Mês
                    </span>
                </div>
                <div class="card-body">
                    <div class="chart-section">
                        <div class="chart-wrap">
                            <canvas id="monthlyChart" height="260"></canvas>
                        </div>
                        <div class="monthly-table">
                            <div class="table-wrap">
                                <table class="data-table" style="min-width:220px;">
                                    <thead>
                                        <tr>
                                            <th>Mês</th>
                                            <th style="text-align:right;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result_total_mes->data_seek(0);
                                        while ($row_mes = $result_total_mes->fetch_assoc()): ?>
                                            <tr>
                                                <td style="font-family:var(--font-mono);font-size:12px;"><?= date('m/Y', strtotime($row_mes['mes'] . '-01')) ?></td>
                                                <td style="text-align:right;font-family:var(--font-mono);font-size:12px;">R$ <?= number_format($row_mes['total_mes'], 2, ',', '.') ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <footer style="margin-top:32px;text-align:center;font-size:12px;color:var(--ink-4);">
                Relatório gerado em <?= date('d/m/Y H:i:s') ?>
            </footer>
        </div>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal-overlay" id="helpModal">
        <div class="modal-box" style="max-width:540px;">
            <h3 class="modal-title"><i class="fas fa-question-circle" style="color:var(--accent);margin-right:8px;"></i>Ajuda — Relatório de Saídas</h3>
            <div class="modal-desc">
                <strong>Como usar:</strong>
                <ul style="margin:8px 0 12px 16px;line-height:1.9;">
                    <li>Use os filtros para encontrar registros específicos</li>
                    <li>Clique em uma coluna para ordenar os resultados</li>
                    <li>Use a barra de pesquisa para encontrar qualquer informação</li>
                    <li>Exporte os dados para Excel quando necessário</li>
                </ul>
                <strong>Dicas:</strong>
                <ul style="margin:8px 0 0 16px;line-height:1.9;">
                    <li>Clique nos badges de filtro ativo para removê-los individualmente</li>
                    <li>Clique em "Limpar" para reiniciar todos os filtros</li>
                    <li>Os valores totais são calculados automaticamente</li>
                </ul>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('helpModal').classList.remove('open')">Fechar</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('overlay').classList.toggle('open');
    }

    $(document).ready(function () {
        // DataTable
        $('#dataTable').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' },
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            pageLength: <?= $registros_por_pagina ?>,
            lengthMenu: [10, 25, 50, 100],
            order: [[7, 'desc']],
            columnDefs: [{ targets: [2, 5], type: 'num-fmt' }]
        });

        // Flatpickr
        $(".flatpickr").flatpickr({ dateFormat: "Y-m-d" });

        // Persist filters in sessionStorage
        $('#filterForm').on('submit', function () {
            ['data_inicio','data_fim','empresa','produto','setor','responsavel'].forEach(function(k) {
                sessionStorage.setItem(k, document.getElementById(k)?.value || '');
            });
        });

        // Sync export link with active filters
        function atualizarLinkExport() {
            var params = new URLSearchParams();
            ['data_inicio','data_fim','empresa','produto','tipo_custo','setor','responsavel'].forEach(function(f) {
                var el = document.getElementById(f);
                if (el && el.value) params.set(f, el.value);
            });
            var qs = params.toString();
            document.getElementById('exportLink').href = 'export_departure.php' + (qs ? '?' + qs : '');
        }
        ['data_inicio','data_fim','empresa','produto','tipo_custo','setor','responsavel'].forEach(function(f) {
            var el = document.getElementById(f);
            if (el) el.addEventListener('change', atualizarLinkExport);
        });
        atualizarLinkExport();
    });

    // Remove individual filter badge
    function removeFilter(type) {
        if (type === 'date') {
            document.getElementById('data_inicio').value = '';
            document.getElementById('data_fim').value = '';
        } else {
            var el = document.getElementById(type);
            if (el) el.value = '';
        }
        document.getElementById('filterForm').submit();
    }

    <?php if ($result_total_mes->num_rows > 0): ?>
    const monthlyData = [
        <?php
        $result_total_mes->data_seek(0);
        while ($row_mes = $result_total_mes->fetch_assoc()):
            echo "{month:'" . date('m/Y', strtotime($row_mes['mes'] . '-01')) . "',total:" . $row_mes['total_mes'] . "},";
        endwhile;
        ?>
    ].reverse();

    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: monthlyData.map(function(d) { return d.month; }),
            datasets: [{
                label: 'Valor Total (R$)',
                data: monthlyData.map(function(d) { return d.total; }),
                backgroundColor: '#3268e4',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return 'R$ ' + ctx.raw.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) {
                            return 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    </script>
</body>
</html>
