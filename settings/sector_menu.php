<?php
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

define('LIMITE_SETORES', 10);
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * LIMITE_SETORES;

try {
    $sqlTotal = "SELECT COUNT(*) AS total FROM setores WHERE ativo = 1";
    $stmtTotal = $mysqli->prepare($sqlTotal);
    if (!$stmtTotal->execute()) {
        throw new Exception("Erro ao contar setores: " . $mysqli->error);
    }
    $resultTotal = $stmtTotal->get_result();
    $totalSetores = $resultTotal->fetch_assoc()['total'];

    $sql = "SELECT * FROM setores WHERE ativo = 1 LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $limiteSetores = LIMITE_SETORES;
    $stmt->bind_param('ii', $limiteSetores, $offset);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar setores: " . $mysqli->error);
    }
    $result = $stmt->get_result();

    $totalPaginas = ceil($totalSetores / LIMITE_SETORES);
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
    <title>Setores</title>
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
                <span class="current">Setores</span>
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
                    <h1 class="page-title"><strong>Lista de</strong> Setores</h1>
                </div>
                <div class="page-header-actions">
                    <a href="sector_create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Setor
                    </a>
                </div>
            </div>

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
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="td-id"><?= $row['id'] ?></td>
                                        <td class="td-name"><?= htmlspecialchars($row['nome']) ?></td>
                                        <td class="td-actions">
                                            <div class="td-actions-wrap">
                                                <a href="sector_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                <a href="sector_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja desativar?')">Desativar</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="table-empty">
                                        <i class="fas fa-sitemap"></i>
                                        <p>Nenhum setor encontrado.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <a href="?pagina=<?= $pagina - 1 ?>" class="page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>">&laquo;</a>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?pagina=<?= $i ?>" class="page-btn <?= $pagina == $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <a href="?pagina=<?= $pagina + 1 ?>" class="page-btn <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">&raquo;</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>
