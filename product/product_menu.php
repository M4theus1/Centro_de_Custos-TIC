<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$busca = isset($_GET['busca']) ? htmlspecialchars(trim($_GET['busca']), ENT_QUOTES, 'UTF-8') : '';

define('LIMITE_PRODUTOS', 10);
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * LIMITE_PRODUTOS;

try {
    $sqlTotal = "SELECT COUNT(*) AS total FROM produtos WHERE nome LIKE ?";
    $stmtTotal = $mysqli->prepare($sqlTotal);
    $buscaLike = '%' . $busca . '%';
    $stmtTotal->bind_param('s', $buscaLike);
    if (!$stmtTotal->execute()) {
        throw new Exception("Erro ao contar produtos: " . $mysqli->error);
    }
    $resultTotal = $stmtTotal->get_result();
    $totalProdutos = $resultTotal->fetch_assoc()['total'];

    $sql = "SELECT * FROM produtos WHERE ativo = 1 AND nome LIKE ? LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $limiteProdutos = LIMITE_PRODUTOS;
    $stmt->bind_param('sii', $buscaLike, $limiteProdutos, $offset);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar produtos: " . $mysqli->error);
    }
    $result = $stmt->get_result();

    $totalPaginas = ceil($totalProdutos / LIMITE_PRODUTOS);
} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage());
    die("Ocorreu um erro. Por favor, tente novamente mais tarde.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
    <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main">
        <header class="topbar">
            <nav class="topbar-breadcrumb">
                <a href="/centro_de_custos/dashboard/painel.php">Início</a>
                <span>/</span>
                <span>Produtos</span>
                <span>/</span>
                <span class="current">Produto</span>
            </nav>
            <div class="topbar-right">
                <span class="topbar-username"><?= htmlspecialchars($primeiro_nome, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="topbar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </header>

        <div class="content">
            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    if (is_array($_SESSION['mensagem'])) {
                        echo implode("<br>", $_SESSION['mensagem']);
                    } else {
                        echo htmlspecialchars($_SESSION['mensagem'], ENT_QUOTES, 'UTF-8');
                    }
                    unset($_SESSION['mensagem']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <span class="page-eyebrow">Produtos</span>
                    <h1 class="page-title"><strong>Menu de</strong> Produtos</h1>
                </div>
                <div class="page-header-actions">
                    <a href="/centro_de_custos/product/product_create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Produto
                    </a>
                </div>
            </div>

            <form method="GET" action="" style="margin-bottom:20px;">
                <div class="search-bar">
                    <div class="search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            name="busca"
                            class="search-input"
                            placeholder="Buscar produto..."
                            value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if (!empty($busca)): ?>
                        <a href="?" class="btn btn-secondary">Limpar</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="card">
                <div class="table-wrap" style="border:none;border-radius:0;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Marca</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($produto = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="td-id"><?= htmlspecialchars($produto['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="td-name"><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($produto['marca'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="td-actions">
                                            <div class="td-actions-wrap">
                                                <a href="product_edit.php?id=<?= urlencode($produto['id']) ?>" class="btn btn-sm btn-warning">Editar</a>
                                                <a href="product_delete.php?id=<?= urlencode($produto['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir este produto?')">Excluir</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="table-empty">
                                        <i class="fas fa-box-open"></i>
                                        <p>
                                            <?php if (!empty($busca)): ?>
                                                Nenhum produto encontrado com o termo "<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>".
                                            <?php else: ?>
                                                Nenhum produto cadastrado.
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <a href="?pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>" class="page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>">&laquo;</a>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>" class="page-btn <?= $pagina == $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="?pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>" class="page-btn <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">&raquo;</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
