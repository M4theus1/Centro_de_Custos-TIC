<?php
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

define('LIMITE_FORNECEDORES', 10);
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * LIMITE_FORNECEDORES;
$limite = LIMITE_FORNECEDORES;
$offsetValue = $offset;

$sqlTotal = "SELECT COUNT(*) AS total FROM fornecedores WHERE ativo = 1";
$resultTotal = $mysqli->query($sqlTotal);
$totalFornecedores = $resultTotal->fetch_assoc()['total'];
$totalPaginas = ceil($totalFornecedores / LIMITE_FORNECEDORES);

$sql = "SELECT * FROM fornecedores WHERE ativo = 1 LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $limite, $offsetValue);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedores</title>
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
                <span>Cadastros</span>
                <span>/</span>
                <span class="current">Fornecedores</span>
            </nav>
            <div class="topbar-right">
                <span class="topbar-username"><?= htmlspecialchars($primeiro_nome, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="topbar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </header>

        <div class="content">
            <div class="page-header">
                <div>
                    <span class="page-eyebrow">Cadastros</span>
                    <h1 class="page-title"><strong>Lista de</strong> Fornecedores</h1>
                </div>
                <div class="page-header-actions">
                    <a href="supplier_create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Fornecedor
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
                                                <a href="supplier_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                <a href="supplier_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja desativar o fornecedor?')">Desativar</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="table-empty">
                                        <i class="fas fa-truck"></i>
                                        <p>Nenhum fornecedor encontrado.</p>
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
