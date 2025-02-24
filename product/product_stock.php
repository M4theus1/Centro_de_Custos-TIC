<?php
include(__DIR__ . '/../config/config.php');

$itens_por_pagina = 10; // Define o número de itens por página
$pagina_atual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$inicio = ($pagina_atual - 1) * $itens_por_pagina;

// Filtragem da pesquisa
$termo_pesquisa = isset($_GET['pesquisa']) ? $mysqli->real_escape_string($_GET['pesquisa']) : '';

$sql_base = "FROM estoque e
             JOIN empresas emp ON e.id_empresa = emp.id
             JOIN produtos p ON e.id_produto = p.id";

if (!empty($termo_pesquisa)) {
    $sql_base .= " WHERE emp.nome LIKE '%$termo_pesquisa%' OR p.nome LIKE '%$termo_pesquisa%'";
}

// Consulta para contar o total de registros
$sql_count = "SELECT COUNT(*) as total $sql_base";
$result_count = $mysqli->query($sql_count);
$total_registros = $result_count->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $itens_por_pagina);

// Consulta para buscar os dados com limite para paginação
$sql = "SELECT e.id_estoque, emp.nome AS empresa, p.nome AS produto, e.quantidade 
        $sql_base 
        LIMIT $inicio, $itens_por_pagina";

$result = $mysqli->query($sql);

if (!$result) {
    die("Erro na consulta: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .table-spreadsheet {
            border: 1px solid #dee2e6;
        }
        .table-spreadsheet th,
        .table-spreadsheet td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }
        .table-spreadsheet th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        body {
            background-color: #f8f9fa;
            display: flex;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #ffffff;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main-content">
        <h1>Estoque</h1>

        <div class="mb-4">
            <a href="/centro_de_custos/product/product_departure.php" class="btn btn-danger">
                REGISTRAR SAÍDA
            </a>
        </div>

        <!-- Barra de pesquisa -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar por empresa ou produto" value="<?php echo htmlspecialchars($termo_pesquisa); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>

        <!-- Tabela de Estoque -->
        <table class="table table-bordered table-spreadsheet">
            <thead>
                <tr>
                    <th>ID Estoque</th>
                    <th>Empresa</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_estoque']); ?></td>
                        <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                        <td><?php echo htmlspecialchars($row['produto']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantidade']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&pesquisa=<?php echo urlencode($termo_pesquisa); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
