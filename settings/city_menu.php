<?php
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
    $sqlTotal = "SELECT COUNT(*) AS total FROM cidades WHERE ativo = 1 AND nome LIKE ?";
    $stmtTotal = $mysqli->prepare($sqlTotal);
    $buscaLike = '%' . $busca . '%';
    $stmtTotal->bind_param('s', $buscaLike);
    if (!$stmtTotal->execute()) {
        throw new Exception("Erro ao contar cidades: " . $mysqli->error);
    }
    $resultTotal = $stmtTotal->get_result();
    $totalCidades = $resultTotal->fetch_assoc()['total'];

    $sql = "SELECT * FROM cidades WHERE ativo = 1 AND nome LIKE ? LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $limiteProdutos = LIMITE_PRODUTOS;
    $stmt->bind_param("sii", $buscaLike, $limiteProdutos, $offset);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar cidades: " . $mysqli->error);
    }
    $result = $stmt->get_result();

    $totalPaginas = ceil($totalCidades / LIMITE_PRODUTOS);
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
    <title>Cidades</title>
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
                <span>Configurações</span>
                <span>/</span>
                <span class="current">Cidades</span>
            </nav>
            <div class="topbar-right">
                <span class="topbar-username"><?= htmlspecialchars($primeiro_nome, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="topbar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </header>

        <div class="content">
            <div class="page-header">
                <div>
                    <span class="page-eyebrow">Configurações</span>
                    <h1 class="page-title"><strong>Lista de</strong> Cidades</h1>
                </div>
                <div class="page-header-actions">
                    <a href="city_create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nova Cidade
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
                            placeholder="Buscar cidade..."
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
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($cidade = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="td-id"><?= htmlspecialchars($cidade['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="td-name"><?= htmlspecialchars($cidade['nome'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="td-actions">
                                            <div class="td-actions-wrap">
                                                <a href="city_edit.php?id=<?= urlencode($cidade['id']) ?>" class="btn btn-sm btn-warning">Editar</a>
                                                <a href="city_delete.php?id=<?= urlencode($cidade['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja excluir esta cidade?')">Excluir</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="table-empty">
                                        <i class="fas fa-city"></i>
                                        <p>
                                            <?php if (!empty($busca)): ?>
                                                Nenhuma cidade encontrada com o termo "<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>".
                                            <?php else: ?>
                                                Nenhuma cidade cadastrada.
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
                <a href="?busca=<?= urlencode($busca) ?>&pagina=<?= $pagina - 1 ?>" class="page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>">&laquo;</a>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?busca=<?= urlencode($busca) ?>&pagina=<?= $i ?>" class="page-btn <?= $pagina == $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="?busca=<?= urlencode($busca) ?>&pagina=<?= $pagina + 1 ?>" class="page-btn <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">&raquo;</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>
