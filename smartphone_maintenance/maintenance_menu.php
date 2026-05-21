<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$filtro_tipo_custo  = isset($_GET['tipo_custo'])  ? $_GET['tipo_custo']  : '';
$filtro_responsavel = isset($_GET['responsavel']) ? $_GET['responsavel'] : '';
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim    = isset($_GET['data_fim'])    ? $_GET['data_fim']    : '';

$where  = [];
$params = [];
$types  = '';

if (!empty($filtro_tipo_custo)) {
    $where[]  = "tipo_custo = ?";
    $params[] = $filtro_tipo_custo;
    $types   .= 's';
}
if (!empty($filtro_responsavel)) {
    $where[]  = "responsavel LIKE ?";
    $params[] = '%' . $filtro_responsavel . '%';
    $types   .= 's';
}
if (!empty($filtro_data_inicio)) {
    $where[]  = "data_servico >= ?";
    $params[] = $filtro_data_inicio;
    $types   .= 's';
}
if (!empty($filtro_data_fim)) {
    $where[]  = "data_servico <= ?";
    $params[] = $filtro_data_fim;
    $types   .= 's';
}

$sql = "SELECT * FROM manutencao_celular";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$query = $stmt->get_result();

$sql_totais = "SELECT tipo_custo, SUM(valor) as total FROM manutencao_celular";
if (!empty($where)) {
    $sql_totais .= " WHERE " . implode(" AND ", $where);
}
$sql_totais .= " GROUP BY tipo_custo";

$stmt_totais = $mysqli->prepare($sql_totais);
if (!empty($params)) {
    $stmt_totais->bind_param($types, ...$params);
}
$stmt_totais->execute();
$totais_query = $stmt_totais->get_result();

$tipos_custo  = $mysqli->query("SELECT DISTINCT tipo_custo FROM manutencao_celular ORDER BY tipo_custo");
$responsaveis = $mysqli->query("SELECT DISTINCT responsavel FROM manutencao_celular ORDER BY responsavel");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Manutenções</title>
    <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
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
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            align-items: end;
        }
        .totals-card { margin-top: 24px; }
        .totals-card table.data-table tfoot th {
            font-size: 13px; color: var(--ink); padding: 12px 18px;
            border-top: 1px solid var(--border-strong);
        }
    </style>
</head>
<body>
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main">
        <header class="topbar">
            <nav class="topbar-breadcrumb">
                <a href="/centro_de_custos/dashboard/painel.php">Início</a>
                <span>/</span>
                <span>Manutenção</span>
                <span>/</span>
                <span class="current">Smartphones</span>
            </nav>
            <div class="topbar-right">
                <span class="topbar-username"><?= htmlspecialchars($primeiro_nome, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="topbar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </header>

        <div class="content">
            <div class="page-header">
                <div>
                    <span class="page-eyebrow">Manutenção</span>
                    <h1 class="page-title"><strong>Lista de</strong> Manutenções</h1>
                </div>
                <div class="page-header-actions">
                    <a href="maintenance_entry.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nova Manutenção
                    </a>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button class="btn btn-secondary" onclick="document.getElementById('helpModal').classList.add('open')">
                        <i class="fas fa-question-circle"></i> Ajuda
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-bar">
                <span class="filter-bar-title">Filtros</span>
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Tipo de Custo</label>
                            <select name="tipo_custo" class="form-control">
                                <option value="">Todos</option>
                                <?php while ($tipo = $tipos_custo->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($tipo['tipo_custo']) ?>" <?= $filtro_tipo_custo == $tipo['tipo_custo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['tipo_custo']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Responsável</label>
                            <select name="responsavel" class="form-control">
                                <option value="">Todos</option>
                                <?php while ($resp = $responsaveis->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($resp['responsavel']) ?>" <?= $filtro_responsavel == $resp['responsavel'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($resp['responsavel']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($filtro_data_inicio) ?>">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($filtro_data_fim) ?>">
                        </div>
                        <div style="display:flex;gap:8px;align-items:flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="?" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabela principal -->
            <div class="card">
                <div class="table-wrap" style="border:none;border-radius:0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Serviço</th>
                                <th>IMEI</th>
                                <th>Responsável</th>
                                <th>Data</th>
                                <th>Valor</th>
                                <th>Tipo de Custo</th>
                                <th>Nota Fiscal</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_geral = 0;
                            if ($query->num_rows > 0):
                                while ($row = $query->fetch_assoc()):
                                    $total_geral += $row['valor'];
                            ?>
                                <tr>
                                    <td class="td-id"><?= $row['id'] ?></td>
                                    <td class="td-name"><?= htmlspecialchars($row['descricao_servico']) ?></td>
                                    <td style="font-family:var(--font-mono);font-size:12px;"><?= $row['imei'] ?></td>
                                    <td><?= htmlspecialchars($row['responsavel']) ?></td>
                                    <td style="font-family:var(--font-mono);font-size:12px;"><?= date('d/m/Y', strtotime($row['data_servico'])) ?></td>
                                    <td style="font-family:var(--font-mono);">R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($row['tipo_custo']) ?></td>
                                    <td>
                                        <?php if (!empty($row['nota_fiscal'])): ?>
                                            <a href="<?= htmlspecialchars($row['nota_fiscal']) ?>" target="_blank" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-file-invoice"></i> Ver
                                            </a>
                                        <?php else: ?>
                                            <span style="color:var(--ink-4);font-size:12px;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="td-actions">
                                        <div class="td-actions-wrap">
                                            <a href="maintenance_entry.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="maintenance_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta manutenção?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="table-empty">
                                        <i class="fas fa-mobile-alt"></i>
                                        <p>Nenhum registro encontrado.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totais por categoria -->
            <?php if ($totais_query->num_rows > 0): ?>
            <div class="card totals-card" style="max-width:480px;">
                <div class="card-header">
                    <span class="card-header-title">
                        <i class="fas fa-calculator"></i> Totais por Categoria
                    </span>
                </div>
                <div style="padding:0;">
                    <div class="table-wrap" style="border:none;border-radius:0;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tipo de Custo</th>
                                    <th style="text-align:right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $soma_totais = 0;
                                while ($total = $totais_query->fetch_assoc()):
                                    $soma_totais += $total['total'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($total['tipo_custo']) ?></td>
                                        <td style="text-align:right;font-family:var(--font-mono);">R$ <?= number_format($total['total'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total Geral</th>
                                    <th style="text-align:right;font-family:var(--font-mono);">R$ <?= number_format($soma_totais, 2, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal-overlay" id="helpModal">
        <div class="modal-box" style="max-width:520px;">
            <h3 class="modal-title"><i class="fas fa-question-circle" style="color:var(--accent);margin-right:8px;"></i>Ajuda</h3>
            <div class="modal-desc">
                <strong>Como usar os filtros:</strong>
                <ul style="margin:8px 0 0 16px;line-height:1.8;">
                    <li>Selecione um <strong>Tipo de Custo</strong> para filtrar por categoria específica</li>
                    <li>Escolha um <strong>Responsável</strong> para ver apenas as manutenções de determinada pessoa</li>
                    <li>Defina um intervalo de datas para buscar manutenções em um período específico</li>
                </ul>
                <strong style="display:block;margin-top:12px;">Exportação de dados:</strong>
                <p style="margin-top:4px;">Use o botão <strong>Imprimir</strong> para gerar uma versão impressão da lista ou salvar como PDF.</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('helpModal').classList.remove('open')">Fechar</button>
            </div>
        </div>
    </div>
</body>
</html>
