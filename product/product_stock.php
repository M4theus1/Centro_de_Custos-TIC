<?php
include(__DIR__ . '/../config/config.php');

$sql = "SELECT e.id_estoque, emp.nome AS empresa, p.nome AS produto, e.quantidade
        FROM estoque e
        JOIN empresas emp ON e.id_empresa = emp.id
        JOIN produtos p ON e.id_produto = p.id";

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
        /* Estilo para a tabela parecer uma planilha */
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
        <!-- Sidebar -->
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main-content">
        <h1>Estoque</h1>

        <!-- Botão "REGISTRAR SAÍDA" -->
        <div class="mb-4">
            <a href="/centro_de_custos/product/product_departure.php" class="btn btn-danger">
                REGISTRAR SAÍDA
            </a>
        </div>

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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>