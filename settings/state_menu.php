<?php
include(__DIR__ . '/../config/config.php');

// Definir o limite de itens por página
define('LIMITE_ESTADOS', 10);

// Obter o número da página atual
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;

// Calcular o deslocamento
$offset = ($pagina - 1) * LIMITE_ESTADOS;

try {
    // Consulta para contar o total de estados
    $sqlTotal = "SELECT COUNT(*) AS total FROM estados WHERE ativo = 1";
    $stmtTotal = $mysqli->prepare($sqlTotal);
    if (!$stmtTotal->execute()) {
        throw new Exception("Erro ao contar estados: " . $mysqli->error);
    }
    $resultTotal = $stmtTotal->get_result();
    $totalEstados = $resultTotal->fetch_assoc()['total'];

    // Consulta para buscar os estados com paginação
    $sql = "SELECT * FROM estados WHERE ativo = 1 LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);

    // Variáveis temporárias para o bind_param
    $limiteEstados = LIMITE_ESTADOS;
    $stmt->bind_param('ii', $limiteEstados, $offset);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar estados: " . $mysqli->error);
    }
    $result = $stmt->get_result();

    // Calcular o total de páginas
    $totalPaginas = ceil($totalEstados / LIMITE_ESTADOS);
} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage()); // Registrar erro no log
    die("Ocorreu um erro. Por favor, tente novamente mais tarde.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Estados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
        <div class="container mt-5">
            <h2 class="mb-4">Lista de Estados</h2>
            <a href="state_create.php" class="btn btn-success mb-3">Adicionar Novo Estado</a>
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Sigla</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['sigla']); ?></td>
                                <td>
                                    <a href="state_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="state_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?');">Desativar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum estado encontrado.</td>
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

                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
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
    </div>

    <!-- jQuery e Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>