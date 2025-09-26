<?php
include(__DIR__ . '/../config/config.php');

// Definir variáveis para o filtro de datas
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Configuração da paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Consulta SQL base com filtro de datas e ordenação por data_entrada DESC
$sql = "SELECT e.id_empresa, emp.nome AS empresa, p.nome AS produto, f.nome AS fornecedor, 
               e.quantidade, e.valor_unitario, e.frete, e.valor_total, e.data_entrada, e.nf, e.observacao 
        FROM entrada_produto e
        JOIN empresas emp ON e.id_empresa = emp.id
        JOIN produtos p ON e.id_produto = p.id
        JOIN fornecedores f ON e.id_fornecedor = f.id
        WHERE 1=1"; // Condição inicial para facilitar a adição de filtros

// Adicionar filtro de datas, se fornecido
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql .= " AND e.data_entrada BETWEEN '$data_inicio' AND '$data_fim'";
}

// Ordenar por data_saída em ordem decrescente(DESC)
$sql .= " ORDER BY e.data_entrada DESC";

// Adicionar paginação
$sql .= " LIMIT $registros_por_pagina OFFSET $offset";

$result = $mysqli->query($sql);

if (!$result) {
    die("Erro na consulta: " . $mysqli->error);
}

// Contagem total de registros para paginação (considerando o filtro de datas)
$sql_total = "SELECT COUNT(*) AS total FROM entrada_produto e WHERE 1=1";
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql_total .= " AND e.data_entrada BETWEEN '$data_inicio' AND '$data_fim'";
}
$result_total = $mysqli->query($sql_total);
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Entradas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --text-color: #5a5c69;
        }
        
        body {
            background-color: var(--secondary-color);
            color: var(--text-color);
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .sidebar {
            height: 100vh;
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--primary-color);
            padding-top: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar a {
            padding: 1rem;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            display: block;
            transition: all 0.3s;
        }
        
        .sidebar a:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar a i {
            margin-right: 0.5rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.35rem;
            font-weight: 600;
            color: #4e73df;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            background-color: white;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.75rem;
            margin-left: 0.25rem;
            border-radius: 0.2rem;
            border: 1px solid #dddfeb;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color);
            color: white !important;
            border: 1px solid var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        
        .btn-success:hover {
            background-color: #17a673;
            border-color: #17a673;
        }
        
        .filter-card {
            margin-bottom: 1.5rem;
        }
        
        .table th {
            background-color: #f8f9fc;
            font-weight: 600;
            color: #5a5c69;
            border-top: 1px solid #e3e6f0;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .badge-success {
            background-color: #1cc88a;
        }
        
        .badge-warning {
            background-color: #f6c23e;
        }
        
        .total-summary {
            background-color: #f8f9fc;
            padding: 1rem;
            border-radius: 0.35rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .sidebar.active {
                width: var(--sidebar-width);
            }
            
            .main-content.active {
                margin-left: var(--sidebar-width);
                width: calc(100% - var(--sidebar-width));
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-box-open mr-2"></i>Relatório de Entradas
            </h1>
            <a href="export_entry.php" class="btn btn-success">
                <i class="fas fa-file-excel mr-2"></i>Exportar para Excel
            </a>
        </div>

        <!-- Filtros -->
        <div class="card filter-card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($data_inicio); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($data_fim); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                        <?php if (!empty($data_inicio) || !empty($data_fim)): ?>
                            <a href="?" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-times mr-2"></i>Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumo -->
        <div class="total-summary">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Registros
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($total_registros, 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Período Selecionado
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                if (!empty($data_inicio) && !empty($data_fim)) {
                                    echo date('d/m/Y', strtotime($data_inicio)) . ' - ' . date('d/m/Y', strtotime($data_fim));
                                } else {
                                    echo 'Todos os períodos';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Página Atual
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $pagina_atual . ' de ' . $total_paginas; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Entradas Registradas</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>Empresa</th>
                                <th>Produto</th>
                                <th>Fornecedor</th>
                                <th>Quantidade</th>
                                <th>Valor Unitário</th>
                                <th>Frete</th>
                                <th>Valor Total</th>
                                <th>Data Entrada</th>
                                <th>Nota Fiscal</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                                    <td><?php echo htmlspecialchars($row['produto']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fornecedor']); ?></td>
                                    <td class="text-right"><?php echo number_format($row['quantidade'], 0, ',', '.'); ?></td>
                                    <td class="text-right">R$ <?php echo number_format($row['valor_unitario'], 2, ',', '.'); ?></td>
                                    <td class="text-right">R$ <?php echo number_format($row['frete'], 2, ',', '.'); ?></td>
                                    <td class="text-right font-weight-bold">R$ <?php echo number_format($row['valor_total'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['nf']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['observacao'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Paginação -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($pagina_atual == 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=1&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item <?php echo ($pagina_atual == 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <?php 
                // Mostrar apenas algumas páginas ao redor da atual
                $start = max(1, $pagina_atual - 2);
                $end = min($total_paginas, $pagina_atual + 2);
                
                if ($start > 1) {
                    echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                }
                
                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor;
                
                if ($end < $total_paginas) {
                    echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                }
                ?>
                
                <li class="page-item <?php echo ($pagina_atual == $total_paginas) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item <?php echo ($pagina_atual == $total_paginas) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configuração do Flatpickr para os campos de data
            flatpickr("#data_inicio", {
                dateFormat: "Y-m-d",
                allowInput: true
            });
            
            flatpickr("#data_fim", {
                dateFormat: "Y-m-d",
                allowInput: true
            });
            
            // Inicialização do DataTable
            $('#dataTable').DataTable({
                paging: false, // Desativa a paginação do DataTable (usaremos a nossa)
                searching: false, // Desativa a busca
                info: false, // Desativa o texto de informações
                ordering: false, // Desativa a ordenação
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Exportar para Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-primary btn-sm'
                    }
                ]
            });
            
            // Botão para mostrar/ocultar a sidebar em dispositivos móveis
            $('.sidebar-toggler').click(function() {
                $('.sidebar').toggleClass('active');
                $('.main-content').toggleClass('active');
            });
        });
    </script>
</body>
</html>