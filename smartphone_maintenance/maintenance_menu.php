<?php
session_start();
include(__DIR__ . '/../config/config.php');

$query = $mysqli->query("SELECT * FROM manutencao_celular");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Manutenções</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include('C:/xampp/htdocs/centro_de_custos/sidebar.php'); ?>

    <div class="container mt-5">
        <h1 class="text-center">Lista de Manutenções</h1>
        <a href="maintenance_entry.php" class="btn btn-success mb-3">Nova Manutenção</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Serviço</th>
                    <th>IMEI</th>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Nota Fiscal</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $query->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['descricao_servico'] ?></td>
                        <td><?= $row['imei'] ?></td>
                        <td><?= date('d/m/Y', strtotime($row['data_servico'])) ?></td>
                        <td>R$<?= number_format($row['valor'], 2, ',', '.') ?></td>
                        <td>
                            <?php if (!empty($row['nota_fiscal'])): ?>
                                <a href="<?= $row['nota_fiscal'] ?>" target="_blank">Ver Nota Fiscal</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="maintenance_entry.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="maintenance_delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?')">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
