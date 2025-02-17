<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Variáveis de busca
$busca = isset($_GET['busca']) ? htmlspecialchars(trim($_GET['busca']), ENT_QUOTES, 'UTF-8') : '';

// Definir o limite de itens por página
define('LIMITE_PRODUTOS', 10);

// Obter o número da página atual
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;

// Calcular o deslocamento
$offset = ($pagina - 1) * LIMITE_PRODUTOS;

try {
    // Consulta para contar o total de produtos
    $sqlTotal = "SELECT COUNT(*) AS total FROM produtos WHERE nome LIKE ?";
    $stmtTotal = $mysqli->prepare($sqlTotal);
    $buscaLike = '%' . $busca . '%';
    $stmtTotal->bind_param('s', $buscaLike);
    if (!$stmtTotal->execute()) {
        throw new Exception("Erro ao contar produtos: " . $mysqli->error);
    }
    $resultTotal = $stmtTotal->get_result();
    $totalProdutos = $resultTotal->fetch_assoc()['total'];

    // Consulta para buscar os produtos
    $sql = "SELECT * FROM produtos WHERE ativo = 1 AND nome LIKE ? LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    
    $limiteProdutos = LIMITE_PRODUTOS;
    $stmt->bind_param('sii', $buscaLike, $limiteProdutos, $offset);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar produtos: " . $mysqli->error);
    }
    $result = $stmt->get_result();

    // Calcular o total de páginas
    $totalPaginas = ceil($totalProdutos / LIMITE_PRODUTOS);
} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage()); // Registrar erro no log
    die("Ocorreu um erro. Por favor, tente novamente mais tarde.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <div class="container menu-container">
        <!-- Mensagem de feedback -->
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-success">
                <?php
                if (is_array($_SESSION['mensagem'])) {
                    echo implode("<br>", $_SESSION['mensagem']); // Exibe cada item do array em uma nova linha
                } else {
                    echo htmlspecialchars($_SESSION['mensagem'], ENT_QUOTES, 'UTF-8'); // Exibe como string
                }
                unset($_SESSION['mensagem']); // Remove a mensagem da sessão
                ?>
            </div>
        <?php endif; ?>

        <div class="menu-header text-center">
            <h1>Menu de Produtos</h1>
        </div>

        <div class="d-flex justify-content-between mt-4 mb-3">
            <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">Voltar ao Dashboard</a>
            <a href="/centro_de_custos/product/product_create.php" class="btn btn-success">Adicionar Novo Produto</a>
        </div>

        <!-- Campo de Busca -->
        <form method="GET" action="" class="mb-4">
            <div class="input-group">
                <input 
                    type="text" 
                    name="busca" 
                    class="form-control" 
                    placeholder="Digite o nome do produto" 
                    value="<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>"
                >
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </div>
            </div>
        </form>

        <div class="table-container">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Marca</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($produto = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produto['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($produto['marca'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="product_edit.php?id=<?php echo urlencode($produto['id']); ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="product_delete.php?id=<?php echo urlencode($produto['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir este produto?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">
                                <?php if (!empty($busca)): ?>
                                    Nenhum produto encontrado com o termo "<?php echo htmlspecialchars($busca, ENT_QUOTES, 'UTF-8'); ?>".
                                <?php else: ?>
                                    Nenhum produto cadastrado.
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
                    <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&busca=<?php echo urlencode($busca); ?>" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                    <li class="page-item <?php if ($pagina == $i) echo 'active'; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&busca=<?php echo urlencode($busca); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php if ($pagina >= $totalPaginas) echo 'disabled'; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&busca=<?php echo urlencode($busca); ?>" aria-label="Próximo">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>