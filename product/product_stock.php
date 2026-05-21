<?php
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$ESTOQUE_MINIMO = 3;

$total_produtos = $mysqli->query("SELECT COUNT(*) as total FROM estoque")->fetch_assoc()['total'];
$estoque_baixo  = $mysqli->query("SELECT COUNT(*) as total FROM estoque WHERE quantidade < $ESTOQUE_MINIMO")->fetch_assoc()['total'];
$total_itens    = $mysqli->query("SELECT SUM(quantidade) as total FROM estoque")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque</title>
    <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ── DataTables theme ─────────────────────────────────── */
        table.dataTable { width: 100% !important; border-collapse: collapse; font-size: 13.5px; }

        table.dataTable thead th,
        table.dataTable thead td {
            padding: 11px 18px;
            font-family: var(--font-mono); font-size: 10.5px; font-weight: 500;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--ink-4); background: var(--paper-2);
            border-bottom: 1px solid var(--border-strong);
            white-space: nowrap;
        }

        table.dataTable tbody td {
            padding: 12px 18px;
            border-bottom: 1px solid var(--border-subtle);
            color: var(--ink-2); vertical-align: middle;
        }

        table.dataTable tbody tr:hover > td { background: var(--paper-2); }
        table.dataTable tbody tr:last-child td { border-bottom: none; }

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
    </style>
</head>
<body>
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main">
        <header class="topbar">
            <nav class="topbar-breadcrumb">
                <a href="/centro_de_custos/dashboard/painel.php">Início</a>
                <span>/</span>
                <span class="current">Estoque</span>
            </nav>
            <div class="topbar-right">
                <span class="topbar-username"><?= htmlspecialchars($primeiro_nome, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="topbar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </header>

        <div class="content">

            <div class="page-header">
                <div>
                    <span class="page-eyebrow">Produtos</span>
                    <h1 class="page-title"><strong>Dashboard</strong> de Estoque</h1>
                </div>
            </div>

            <!-- Resumos -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-dot"></div>
                    <div>
                        <span class="stat-label">Total de Produtos</span>
                        <span class="stat-value"><?= (int) $total_produtos ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-dot amber"></div>
                    <div>
                        <span class="stat-label">Estoque Baixo</span>
                        <span class="stat-value"><?= (int) $estoque_baixo ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-dot green"></div>
                    <div>
                        <span class="stat-label">Total de Itens</span>
                        <span class="stat-value"><?= (int) ($total_itens ?? 0) ?></span>
                    </div>
                </div>
            </div>

            <!-- Gráfico -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <span class="card-header-title">
                        <i class="fa fa-chart-bar"></i> Distribuição de Estoque
                    </span>
                </div>
                <div class="card-body">
                    <canvas id="graficoEstoque" style="max-height: 320px;"></canvas>
                </div>
            </div>

            <!-- Tabela -->
            <div class="card">
                <div class="card-header">
                    <span class="card-header-title">
                        <i class="fa fa-boxes"></i> Estoque por Produto
                    </span>
                </div>
                <div class="card-body">
                    <table id="tabelaEstoque" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empresa</th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function () {
        $('#tabelaEstoque').DataTable({
            ajax: { url: '../api/estoque_list.php', dataSrc: 'data' },
            columns: [
                { data: 'id_estoque' },
                { data: 'empresa' },
                { data: 'produto' },
                { data: 'quantidade' },
                { data: 'status' }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' }
        });
    });

    $.get('../api/estoque_alerta.php', function (data) {
        if (data.total > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Estoque baixo',
                text: data.total + ' produto(s) com estoque crítico'
            });
        }
    });

    $.get('../api/estoque_grafico.php', function (data) {
        const ctx = document.getElementById('graficoEstoque').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Quantidade em estoque',
                    data: data.valores,
                    backgroundColor: '#3268e4'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }, 'json');
    </script>
</body>
</html>
