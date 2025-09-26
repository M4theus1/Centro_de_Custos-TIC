<?php 
include(__DIR__ . '/../config/config.php');

// Definir variáveis para o filtro de datas
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Configuração da paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Consulta SQL base
$sql = "SELECT s.id_saida, 
            emp.nome AS empresa, 
            p.nome AS produto, 
            s.valor_unitario,
            setr.nome AS setor, 
            s.responsavel, 
            s.quantidade, 
            s.data_saida, 
            s.numero_ticket, 
            c.nome AS cidade, 
            e.nome AS estado, 
            s.observacao
        FROM saida_produto s
        JOIN empresas emp ON s.id_empresa = emp.id
        JOIN produtos p ON s.id_produto = p.id
        JOIN setores setr ON s.id_setor = setr.id
        JOIN cidades c ON s.id_cidade = c.id
        JOIN estados e ON s.id_estado = e.id
        WHERE 1=1";

$params = [];
$types = "";

// Adicionar filtro de datas, se fornecido
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql .= " AND s.data_saida BETWEEN ? AND ?";
    $params[] = $data_inicio;
    $params[] = $data_fim;
    $types .= "ss";
}

// Adicionar paginação no final da consulta
$sql .= " ORDER BY s.data_saida DESC, s.id_saida ASC LIMIT ? OFFSET ?";
$params[] = $registros_por_pagina;
$params[] = $offset;
$types .= "ii";

// Preparar a consulta SQL
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    die("Erro na preparação da consulta: " . $mysqli->error);
}

// Vincular parâmetros
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Executar a consulta
$stmt->execute();
$result = $stmt->get_result();

// Contagem total de registros para paginação
$sql_total = "SELECT COUNT(*) AS total FROM saida_produto s WHERE 1=1";
$params_total = [];
$types_total = "";

if (!empty($data_inicio) && !empty($data_fim)) {
    $sql_total .= " AND s.data_saida BETWEEN ? AND ?";
    $params_total[] = $data_inicio;
    $params_total[] = $data_fim;
    $types_total .= "ss";
}

$stmt_total = $mysqli->prepare($sql_total);
if ($stmt_total === false) {
    die("Erro na preparação da consulta de contagem: " . $mysqli->error);
}

if (!empty($params_total)) {
    $stmt_total->bind_param($types_total, ...$params_total);
}

$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = max(1, ceil($total_registros / $registros_por_pagina));

// Consulta para calcular o valor total de saídas por mês
$sql_total_mes = "SELECT 
                    DATE_FORMAT(s.data_saida, '%Y-%m') AS mes, 
                    SUM(s.quantidade * s.valor_unitario) AS total_mes
                  FROM saida_produto s
                  WHERE 1=1";

// Adicionar filtro de datas, se fornecido
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql_total_mes .= " AND s.data_saida BETWEEN ? AND ?";
}

$sql_total_mes .= " GROUP BY DATE_FORMAT(s.data_saida, '%Y-%m')
                    ORDER BY mes DESC";

// Preparar e executar a consulta
$stmt_total_mes = $mysqli->prepare($sql_total_mes);
if ($stmt_total_mes === false) {
    die("Erro na preparação da consulta de total por mês: " . $mysqli->error);
}

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
        }
        
        body {
            background-color: var(--light-bg);
            display: flex;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--dark-bg);
            padding-top: 20px;
            transition: all 0.3s;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar a {
            color: #fff;
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover {
            background-color: #495057;
            border-left: 3px solid var(--primary-color);
        }
        
        .sidebar a.active {
            background-color: #495057;
            border-left: 3px solid var(--primary-color);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 25px;
            width: calc(100% - 250px);
            transition: all 0.3s;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .filter-form {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        
        .btn-excel {
            background-color: #1d6f42;
            border-color: #1d6f42;
        }
        
        .btn-excel:hover {
            background-color: #165a35;
            border-color: #165a35;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        table.dataTable {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 10px;
            margin-left: 2px;
            border-radius: 4px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            color: white !important;
            border: none;
        }
        
        .total-card {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .total-card h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .total-card .value {
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .chart-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .badge-filter {
            background-color: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 20px;
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-flex;
            align-items: center;
        }
        
        .badge-filter i {
            margin-left: 5px;
            cursor: pointer;
        }
        
        @media(max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            
            .card, .filter-form {
                padding: 15px;
            }
        }
        
        /* Animation for table rows */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        tbody tr {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Hover effect for table rows */
        tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1) !important;
            transition: background-color 0.2s;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #b8b8b8;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a0a0a0;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><i class="fas fa-file-alt me-2"></i>Relatório de Saídas</h1>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="fas fa-question-circle"></i> Ajuda
                </button>
            </div>
        </div>

        <!-- Help Modal -->
        <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="helpModalLabel"><i class="fas fa-question-circle me-2"></i>Ajuda - Relatório de Saídas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Como usar:</h6>
                        <ul>
                            <li>Use os filtros para encontrar registros específicos</li>
                            <li>Clique em uma coluna para ordenar os resultados</li>
                            <li>Use a barra de pesquisa para encontrar qualquer informação</li>
                            <li>Exporte os dados para Excel quando necessário</li>
                        </ul>
                        <h6 class="mt-3">Dicas:</h6>
                        <ul>
                            <li>Você pode redimensionar as colunas arrastando suas bordas</li>
                            <li>Clique no botão "Limpar Filtros" para reiniciar sua busca</li>
                            <li>Os valores totais são calculados automaticamente</li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="total-card">
                    <h3><i class="fas fa-box-open me-2"></i>Total de Itens</h3>
                    <div class="value"><?= number_format($total_registros, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="total-card" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                    <h3><i class="fas fa-calendar-alt me-2"></i>Período Selecionado</h3>
                    <div class="value">
                        <?= !empty($data_inicio) ? date('d/m/Y', strtotime($data_inicio)) : 'Início' ?> 
                        - 
                        <?= !empty($data_fim) ? date('d/m/Y', strtotime($data_fim)) : 'Fim' ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="total-card" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                    <h3><i class="fas fa-money-bill-wave me-2"></i>Valor Total</h3>
                    <div class="value">
                        <?php 
                            $total_value = 0;
                            $result->data_seek(0); // Reset pointer to beginning
                            while ($row = $result->fetch_assoc()) {
                                $total_value += $row['quantidade'] * $row['valor_unitario'];
                            }
                            echo 'R$ ' . number_format($total_value, 2, ',', '.');
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card filter-form">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-filter me-2"></i>Filtros</h5>
                
                <!-- Active Filters -->
                <div class="mb-3" id="activeFilters">
                    <?php if (!empty($data_inicio) || !empty($data_fim) || !empty($_GET['empresa']) || !empty($_GET['produto']) || !empty($_GET['setor']) || !empty($_GET['responsavel'])): ?>
                        <p class="mb-2"><small>Filtros ativos:</small></p>
                        <?php if (!empty($data_inicio) && !empty($data_fim)): ?>
                            <span class="badge-filter">
                                Data: <?= date('d/m/Y', strtotime($data_inicio)) ?> - <?= date('d/m/Y', strtotime($data_fim)) ?>
                                <i class="fas fa-times remove-filter" data-filter="date"></i>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($_GET['empresa'])): ?>
                            <span class="badge-filter">
                                Empresa: <?= htmlspecialchars($_GET['empresa']) ?>
                                <i class="fas fa-times remove-filter" data-filter="empresa"></i>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($_GET['produto'])): ?>
                            <span class="badge-filter">
                                Produto: <?= htmlspecialchars($_GET['produto']) ?>
                                <i class="fas fa-times remove-filter" data-filter="produto"></i>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($_GET['setor'])): ?>
                            <span class="badge-filter">
                                Setor: <?= htmlspecialchars($_GET['setor']) ?>
                                <i class="fas fa-times remove-filter" data-filter="setor"></i>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($_GET['responsavel'])): ?>
                            <span class="badge-filter">
                                Responsável: <?= htmlspecialchars($_GET['responsavel']) ?>
                                <i class="fas fa-times remove-filter" data-filter="responsavel"></i>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" id="data_inicio" name="data_inicio" class="form-control flatpickr" value="<?= htmlspecialchars($data_inicio) ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" id="data_fim" name="data_fim" class="form-control flatpickr" value="<?= htmlspecialchars($data_fim) ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="empresa" class="form-label">Empresa</label>
                            <input type="text" id="empresa" name="empresa" class="form-control" placeholder="Nome da empresa" value="<?= htmlspecialchars($_GET['empresa'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="produto" class="form-label">Produto</label>
                            <input type="text" id="produto" name="produto" class="form-control" placeholder="Nome do produto" value="<?= htmlspecialchars($_GET['produto'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="setor" class="form-label">Setor</label>
                            <input type="text" id="setor" name="setor" class="form-control" placeholder="Nome do setor" value="<?= htmlspecialchars($_GET['setor'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="responsavel" class="form-label">Responsável</label>
                            <input type="text" id="responsavel" name="responsavel" class="form-control" placeholder="Responsável" value="<?= htmlspecialchars($_GET['responsavel'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-lg-6 d-flex align-items-end justify-content-between">
                            <button type="submit" class="btn btn-primary w-50 me-2">
                                <i class="fas fa-search me-2"></i> Filtrar
                            </button>
                            <a href="export_departure.php" class="btn btn-excel w-50 text-white">
                                <i class="fas fa-file-excel me-2"></i> Exportar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Registros de Saída</h5>
                <div>
                    <span class="badge bg-light text-dark">
                        <?= $total_registros ?> registro(s) encontrado(s)
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="dataTable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Produto</th>
                                <th>Valor Unitário</th>
                                <th>Setor</th>
                                <th>Responsável</th>
                                <th>Quantidade</th>
                                <th>Data Saída</th>
                                <th>Nº Ticket</th>
                                <th>Cidade</th>
                                <th>Estado</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $result->data_seek(0); // Reset pointer to beginning
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['empresa']) ?></td>
                                    <td><?= htmlspecialchars($row['produto']) ?></td>
                                    <td data-order="<?= $row['valor_unitario'] ?>">R$ <?= number_format($row['valor_unitario'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($row['setor']) ?></td>
                                    <td><?= htmlspecialchars($row['responsavel']) ?></td>
                                    <td data-order="<?= $row['quantidade'] ?>"><?= number_format($row['quantidade'], 0, ',', '.') ?></td>
                                    <td data-order="<?= strtotime($row['data_saida']) ?>"><?= date('d/m/Y', strtotime($row['data_saida'])) ?></td>
                                    <td><?= htmlspecialchars($row['numero_ticket']) ?></td>
                                    <td><?= htmlspecialchars($row['cidade']) ?></td>
                                    <td><?= htmlspecialchars($row['estado']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['observacao'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monthly Totals Section -->
        <?php if ($result_total_mes->num_rows > 0): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Valor Total de Saídas por Mês</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="chart-container">
                                <canvas id="monthlyChart" height="300"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mês</th>
                                            <th class="text-end">Valor Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $result_total_mes->data_seek(0); // Reset pointer to beginning
                                        while ($row_mes = $result_total_mes->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('m/Y', strtotime($row_mes['mes'] . '-01')) ?></td>
                                                <td class="text-end">R$ <?= number_format($row_mes['total_mes'], 2, ',', '.') ?></td>
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

        <!-- Footer -->
        <footer class="mt-5 text-center text-muted">
            <small>Relatório gerado em <?= date('d/m/Y H:i:s') ?></small>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/pt-br.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize date picker
            $(".flatpickr").flatpickr({
                dateFormat: "Y-m-d",
                locale: "pt"
            });
            
            // Initialize DataTable
            $('#dataTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                pageLength: <?= $registros_por_pagina ?>,
                lengthMenu: [10, 25, 50, 100],
                order: [[6, 'desc']], // Default sort by date descending
                columnDefs: [
                    { targets: [2, 5], type: 'num-fmt' } // For numeric sorting with formatting
                ]
            });
            
            // Remove filter functionality
            $('.remove-filter').click(function() {
                const filterType = $(this).data('filter');
                
                if (filterType === 'date') {
                    $('#data_inicio').val('');
                    $('#data_fim').val('');
                } else {
                    $('#' + filterType).val('');
                }
                
                $('form').submit();
            });
            
            // Initialize chart if monthly data exists
            <?php if ($result_total_mes->num_rows > 0): ?>
                const monthlyData = [
                    <?php 
                    $result_total_mes->data_seek(0); // Reset pointer to beginning
                    while ($row_mes = $result_total_mes->fetch_assoc()): 
                        echo "{month: '".date('m/Y', strtotime($row_mes['mes'] . '-01'))."', total: ".$row_mes['total_mes']."},";
                    endwhile; 
                    ?>
                ].reverse(); // Reverse to show in chronological order
                
                const ctx = document.getElementById('monthlyChart').getContext('2d');
                const monthlyChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: monthlyData.map(item => item.month),
                        datasets: [{
                            label: 'Valor Total (R$)',
                            data: monthlyData.map(item => item.total),
                            backgroundColor: 'rgba(52, 152, 219, 0.7)',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'R$ ' + context.raw.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        }
                    }
                });
            <?php endif; ?>
            
            // Store filter values in session storage
            $('form').on('submit', function() {
                sessionStorage.setItem('data_inicio', $('#data_inicio').val());
                sessionStorage.setItem('data_fim', $('#data_fim').val());
                sessionStorage.setItem('empresa', $('#empresa').val());
                sessionStorage.setItem('produto', $('#produto').val());
                sessionStorage.setItem('setor', $('#setor').val());
                sessionStorage.setItem('responsavel', $('#responsavel').val());
            });
            
            // Load filter values from session storage
            $(window).on('load', function() {
                if (sessionStorage.getItem('data_inicio')) {
                    $('#data_inicio').val(sessionStorage.getItem('data_inicio'));
                }
                if (sessionStorage.getItem('data_fim')) {
                    $('#data_fim').val(sessionStorage.getItem('data_fim'));
                }
                if (sessionStorage.getItem('empresa')) {
                    $('#empresa').val(sessionStorage.getItem('empresa'));
                }
                if (sessionStorage.getItem('produto')) {
                    $('#produto').val(sessionStorage.getItem('produto'));
                }
                if (sessionStorage.getItem('setor')) {
                    $('#setor').val(sessionStorage.getItem('setor'));
                }
                if (sessionStorage.getItem('responsavel')) {
                    $('#responsavel').val(sessionStorage.getItem('responsavel'));
                }
            });
        });
    </script>
</body>
</html>