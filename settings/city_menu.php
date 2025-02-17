<?php
include(__DIR__ . '/../config/config.php');

// Variáveis de busca
$busca = isset($_GET['busca']) ? htmlspecialchars(trim($_GET['busca']), ENT_QUOTES, 'UTF-8') : '';

// Definir o limite de itens por página
define('LIMITE_PRODUTOS', 10);

// Obter o número da página atual
$pagina = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;

// Calcular o deslocamento
$offset = ($pagina - 1) * LIMITE_PRODUTOS;

try {
    // Consulta para contar o total de cidades
    $sqlTotal = "SELECT COUNT(*) AS total FROM cidades WHERE ativo = 1 AND nome LIKE ?";
    $stmtTotal = $mysqli->prepare($sqlTotal);
    $buscaLike = '%' . $busca . '%';
    $stmtTotal->bind_param('s', $buscaLike);

    if (!$stmtTotal->execute()) {
        throw new Exception("Erro ao contar cidades: " . $mysqli->error);
    }

    $resultTotal = $stmtTotal->get_result();
    $totalCidades = $resultTotal->fetch_assoc()['total'];

    // Consulta para buscar as cidades
    $sql = "SELECT * FROM cidades WHERE ativo = 1 AND nome LIKE ? LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $limiteProdutos = LIMITE_PRODUTOS;
    $stmt->bind_param("sii", $buscaLike, $limiteProdutos, $offset);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar cidades: " . $mysqli->error);
    }

    $result = $stmt->get_result();

    // Calcular o total de páginas
    $totalPaginas = ceil($totalCidades / LIMITE_PRODUTOS);
} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage()); // Registrar erro no log
    die("Ocorreu um erro. Por favor, tente novamente mais tarde.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Cidades</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        .menu-container {
            margin-top: 50px;
        }
        .menu-header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            border-radius: 5px;
        }
        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main-content">
        <div class="container mt-5">
            <h2 class="mb-4">Lista de Cidades</h2>
            <a href="city_create.php" class="btn btn-success mb-3">Adicionar Nova Cidade</a>

            <!-- Campo de Busca -->
            <form method="GET" action="" class="mb-4">
                <div class="input-group">
                    <input 
                        type="text"
                        name="busca"
                        class="form-control"
                        placeholder="Digite o nome da cidade"
                        value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </div>
            </form>

            <div class="table-container">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($cidade = $result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cidade['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($cidade['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <a href="city_edit.php?id=<?php echo urlencode($cidade['id']); ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="city_delete.php?id=<?php echo urlencode($cidade['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir esta cidade?');">Excluir</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">
                                    <?php if (!empty($busca)): ?>
                                        Nenhuma cidade encontrada com o termo "<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>".
                                    <?php else: ?>
                                        Nenhuma cidade cadastrada.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>               
                </table>
            </div>

            <!-- Paginação -->
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if ($pagina <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?busca=<?php echo urlencode($busca); ?>&pagina=<?php echo $pagina - 1; ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                        <li class="page-item <?php echo ($i == $pagina) ? 'active' : ''; ?>">
                            <a class="page-link" href="?busca=<?php echo urlencode($busca); ?>&pagina=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php if ($pagina >= $totalPaginas) echo 'disabled'; ?>">
                        <a class="page-link" href="?busca=<?php echo urlencode($busca); ?>&pagina=<?php echo $pagina + 1; ?>" aria-label="Próximo">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>