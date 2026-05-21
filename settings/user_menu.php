<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

define('LIMITE_USUARIOS', 10);
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * LIMITE_USUARIOS;

try {
    $sqlTotal = "SELECT COUNT(*) AS total FROM usuarios WHERE ativo = 1";
    $stmtTotal = $mysqli->prepare($sqlTotal);
    if (!$stmtTotal->execute()) {
        throw new Exception("Erro ao contar usuários: " . $mysqli->error);
    }
    $resultTotal = $stmtTotal->get_result();
    $totalUsuarios = $resultTotal->fetch_assoc()['total'];

    $sql = "SELECT * FROM usuarios WHERE ativo = 1 LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);
    $limiteUsuarios = LIMITE_USUARIOS;
    $stmt->bind_param('ii', $limiteUsuarios, $offset);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar usuários: " . $mysqli->error);
    }
    $result = $stmt->get_result();

    $totalPaginas = ceil($totalUsuarios / LIMITE_USUARIOS);
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
    <title>Usuários</title>
    <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .data-table td.td-date { font-family: var(--font-mono); font-size: 12px; color: var(--ink-3); }
        .data-table td.td-level { font-family: var(--font-mono); font-size: 12px; }
    </style>
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
                <span class="current">Usuários</span>
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
                    <h1 class="page-title"><strong>Lista de</strong> Usuários</h1>
                </div>
                <div class="page-header-actions">
                    <a href="user_create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Usuário
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
                                <th>E-mail</th>
                                <th>Nível</th>
                                <th>Ativo</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="td-id"><?= $row['id'] ?></td>
                                        <td class="td-name"><?= htmlspecialchars($row['nome']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td class="td-level"><?= $row['nivel_acesso'] ?></td>
                                        <td>
                                            <?php if ($row['ativo']): ?>
                                                <span class="badge badge-success">Sim</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Não</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="td-date"><?= htmlspecialchars($row['criado_em']) ?></td>
                                        <td class="td-actions" style="width:210px;">
                                            <div class="td-actions-wrap">
                                                <a href="user_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                                <a href="user_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja desativar?')">Desativar</a>
                                                <a href="user_reset.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Tem certeza que deseja resetar a senha?')">Resetar</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="table-empty">
                                        <i class="fas fa-users"></i>
                                        <p>Nenhum usuário encontrado.</p>
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
