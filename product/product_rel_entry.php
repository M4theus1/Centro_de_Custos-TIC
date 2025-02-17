<?php
include(__DIR__ . '/../config/config.php');

// Definir variáveis para o filtro de datas
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Configuração da paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Consulta SQL base com filtro de datas e ordenação por data_entrada ASC
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

// Ordenar por data_entrada em ordem ascendente (ASC)
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
    <!-- Incluir o colResizable -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/colresizable/1.6.0/colResizable.min.css">
    <style>
        /* Estilo para a tabela ter tamanho fixo */
        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }
        .table-fixed th, .table-fixed td {
            overflow: hidden; /* Oculta o conteúdo que ultrapassar */
            text-overflow: ellipsis; /* Adiciona "..." ao texto que ultrapassar */
            white-space: nowrap; /* Impede a quebra de linha */
        }
        .table-container {
            overflow-x: auto; /* Permite rolagem horizontal */
        }
        .table-spreadsheet {
            border: 1px solid #dee2e6;
        }
        .table-spreadsheet th, .table-spreadsheet td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }
        .table-spreadsheet th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .container {
            max-width: 95%;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .filter-form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Barra de Navegação -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/centro_de_custos/dashboard/painel.php">Centro de Custos TIC</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Produtos
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="/centro_de_custos/product/product_stock.php">Estoque</a>       
                        <a class="dropdown-item" href="/centro_de_custos/product/product_menu.php">Produto</a>
                        <a class="dropdown-item" href="/centro_de_custos/product/product_entry.php">Entrada</a> 
                        <a class="dropdown-item" href="/centro_de_custos/product/product_departure.php">Saída</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/centro_de_custos/settings/supplier_menu.php">Fornecedores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/centro_de_custos/settings/user_menu.php">Usuários</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Configurações
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="/centro_de_custos/settings/enterprise_menu.php">Empresas</a>
                        <a class="dropdown-item" href="/centro_de_custos/settings/state_menu.php">Estados</a>
                        <a class="dropdown-item" href="/centro_de_custos/settings/city_menu.php">Cidades</a>
                        <a class="dropdown-item" href="/centro_de_custos/settings/sector_menu.php">Setores</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Relatório de Entradas</h1>

        <!-- Formulário de Filtro por Datas -->
        <form method="GET" action="" class="filter-form">
            <div class="row">
                <div class="col-md-4">
                    <label for="data_inicio">Data Início:</label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($data_inicio); ?>">
                </div>
                <div class="col-md-4">
                    <label for="data_fim">Data Fim:</label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($data_fim); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Container da Tabela com Rolagem Horizontal -->
        <div class="table-container">
            <table class="table table-bordered table-spreadsheet table-fixed" id="resizableTable">
                <thead>
                    <tr>
                        <th style="width: 10%;">Empresa</th>
                        <th style="width: 10%;">Produto</th>
                        <th style="width: 10%;">Fornecedor</th>
                        <th style="width: 8%;">Quantidade</th>
                        <th style="width: 10%;">Valor Unitário</th>
                        <th style="width: 8%;">Frete</th>
                        <th style="width: 10%;">Valor Total</th>
                        <th style="width: 10%;">Data Entrada</th>
                        <th style="width: 10%;">Nota Fiscal</th>
                        <th style="width: 14%;">Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                            <td><?php echo htmlspecialchars($row['produto']); ?></td>
                            <td><?php echo htmlspecialchars($row['fornecedor']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantidade']); ?></td>
                            <td>R$ <?php echo number_format($row['valor_unitario'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($row['frete'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($row['valor_total'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
                            <td><?php echo htmlspecialchars($row['nf']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['observacao'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <!-- Incluir o colResizable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/colresizable/1.6.0/colResizable.min.js"></script>
    <script>
        // Aplicar colResizable à tabela
        $(document).ready(function() {
            $("#resizableTable").colResizable({
                liveDrag: true, // Permite redimensionamento em tempo real
                gripInnerHtml: "<div class='grip'></div>", // Estilo do "grip"
                minWidth: 50 // Largura mínima das colunas
            });
        });
    </script>
</body>
</html>