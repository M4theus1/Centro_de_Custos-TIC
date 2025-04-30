<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Inicializa filtros
$filtro_tipo_custo = isset($_GET['tipo_custo']) ? $_GET['tipo_custo'] : '';
$filtro_responsavel = isset($_GET['responsavel']) ? $_GET['responsavel'] : '';
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Construir a query com filtros
$where = [];
$params = [];
$types = '';

if (!empty($filtro_tipo_custo)) {
    $where[] = "tipo_custo = ?";
    $params[] = $filtro_tipo_custo;
    $types .= 's';
}

if (!empty($filtro_responsavel)) {
    $where[] = "responsavel LIKE ?";
    $params[] = '%' . $filtro_responsavel . '%';
    $types .= 's';
}

if (!empty($filtro_data_inicio)) {
    $where[] = "data_servico >= ?";
    $params[] = $filtro_data_inicio;
    $types .= 's';
}

if (!empty($filtro_data_fim)) {
    $where[] = "data_servico <= ?";
    $params[] = $filtro_data_fim;
    $types .= 's';
}

$sql = "SELECT * FROM manutencao_celular";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Preparar e executar a query
$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$query = $stmt->get_result();

// Query para totais por categoria
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

// Obter tipos de custo distintos para o dropdown
$tipos_custo = $mysqli->query("SELECT DISTINCT tipo_custo FROM manutencao_celular ORDER BY tipo_custo");
// Obter responsáveis distintos para o dropdown
$responsaveis = $mysqli->query("SELECT DISTINCT responsavel FROM manutencao_celular ORDER BY responsavel");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Manutenções</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            padding-top: 60px;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 60px;
            z-index: 1000;
            overflow-x: hidden;
            transition: 0.3s;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 0;
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        .filter-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-responsive {
            overflow-x: auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .total-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.03);
        }
        
        @media (max-width: 768px) {
            .btn-action {
                margin-bottom: 5px;
                display: block;
                width: 100%;
            }
            
            .filter-card .row > div {
                margin-bottom: 15px;
            }
        }
        
        .navbar {
            z-index: 1100;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include('C:/xampp/htdocs/centro_de_custos/sidebar.php'); ?>
    
    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div class="container-fluid">
            <h1 class="text-center mb-4">Lista de Manutenções</h1>
            
            <!-- Filtros -->
            <div class="filter-card">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="tipo_custo" class="form-label">Tipo de Custo</label>
                            <select class="form-select" id="tipo_custo" name="tipo_custo">
                                <option value="">Todos</option>
                                <?php while($tipo = $tipos_custo->fetch_assoc()): ?>
                                    <option value="<?= $tipo['tipo_custo'] ?>" <?= $filtro_tipo_custo == $tipo['tipo_custo'] ? 'selected' : '' ?>>
                                        <?= $tipo['tipo_custo'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="responsavel" class="form-label">Responsável</label>
                            <select class="form-select" id="responsavel" name="responsavel">
                                <option value="">Todos</option>
                                <?php while($resp = $responsaveis->fetch_assoc()): ?>
                                    <option value="<?= $resp['responsavel'] ?>" <?= $filtro_responsavel == $resp['responsavel'] ? 'selected' : '' ?>>
                                        <?= $resp['responsavel'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= $filtro_data_inicio ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= $filtro_data_fim ?>">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="?" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="d-flex justify-content-between mb-3">
                <a href="maintenance_entry.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nova Manutenção
                </a>
                <div>
                    <button class="btn btn-info me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#helpModal">
                        <i class="fas fa-question-circle"></i> Ajuda
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
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
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['descricao_servico']) ?></td>
                                    <td><?= $row['imei'] ?></td>
                                    <td><?= htmlspecialchars($row['responsavel']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['data_servico'])) ?></td>
                                    <td>R$ <?= number_format($row['valor'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($row['tipo_custo']) ?></td>
                                    <td>
                                        <?php if (!empty($row['nota_fiscal'])): ?>
                                            <a href="<?= htmlspecialchars($row['nota_fiscal']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-invoice"></i> Ver Nota
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap">
                                            <a href="maintenance_entry.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm me-1 mb-1 btn-action">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="maintenance_delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm mb-1 btn-action" onclick="return confirm('Tem certeza que deseja excluir esta manutenção?')">
                                                <i class="fas fa-trash-alt"></i> Excluir
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Nenhum registro encontrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Totais por categoria -->
            <div class="row">
                <div class="col-md-6">
                    <div class="total-card">
                        <h5><i class="fas fa-calculator me-2"></i>Totais por Categoria</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tipo de Custo</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $soma_totais = 0;
                                if ($totais_query->num_rows > 0):
                                    while ($total = $totais_query->fetch_assoc()): 
                                        $soma_totais += $total['total'];
                                ?>
                                        <tr>
                                            <td><?= htmlspecialchars($total['tipo_custo']) ?></td>
                                            <td class="text-end">R$ <?= number_format($total['total'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Nenhum total disponível</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th>Total Geral</th>
                                    <th class="text-end">R$ <?= number_format($soma_totais, 2, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Ajuda -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="helpModalLabel"><i class="fas fa-question-circle me-2"></i>Ajuda</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Como usar os filtros:</h6>
                    <ul>
                        <li>Selecione um <strong>Tipo de Custo</strong> para filtrar por categoria específica</li>
                        <li>Escolha um <strong>Responsável</strong> para ver apenas as manutenções de determinada pessoa</li>
                        <li>Defina um intervalo de datas para buscar manutenções em um período específico</li>
                    </ul>
                    <h6 class="mt-3">Exportação de dados:</h6>
                    <p>Use o botão <strong>Imprimir</strong> para gerar uma versão impressão da lista ou salvar como PDF.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ativa tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Botão para mostrar/esconder sidebar em telas pequenas
            document.querySelector('.navbar-toggler').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('show');
                document.querySelector('.main-content').classList.toggle('show');
            });
        });
    </script>
</body>
</html>