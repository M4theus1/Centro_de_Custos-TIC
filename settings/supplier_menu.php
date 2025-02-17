<?php
include(__DIR__ . '/../config/config.php');

// Definir o limite de fornecedores por página
// Definir o limite de fornecedores por página
define('LIMITE_FORNECEDORES', 10);

// Obter o número da página atual
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;

// Calcular o deslocamento (OFFSET)
$offset = ($pagina - 1) * LIMITE_FORNECEDORES;

// Armazenar valores em variáveis para evitar erro no bind_param()
$limite = LIMITE_FORNECEDORES;
$offsetValue = $offset;

// Contar o total de fornecedores ativos
$sqlTotal = "SELECT COUNT(*) AS total FROM fornecedores WHERE ativo = 1";
$resultTotal = $mysqli->query($sqlTotal);
$totalFornecedores = $resultTotal->fetch_assoc()['total'];

// Calcular total de páginas
$totalPaginas = ceil($totalFornecedores / LIMITE_FORNECEDORES);

// Buscar fornecedores com paginação
$sql = "SELECT * FROM fornecedores WHERE ativo = 1 LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $limite, $offsetValue); // Agora passamos variáveis normais
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Fornecedores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
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
        <h2 class="mb-4">Lista de Fornecedores</h2>
        <a href="supplier_create.php" class="btn btn-success mb-3">Adicionar Novo Fornecedor</a>

        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td>
                                <a href="supplier_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                <a href="supplier_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir o fornecedor?');">Desativar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">Nenhum fornecedor encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($pagina <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                    <li class="page-item <?php if ($pagina == $i) echo 'active'; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php if ($pagina >= $totalPaginas) echo 'disabled'; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>" aria-label="Próximo">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $mysqli->close(); ?>
