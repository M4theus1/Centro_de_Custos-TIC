<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes   = explode(' ', trim($nome_usuario));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = trim($_POST['nome']         ?? '');
    $email       = trim($_POST['email']        ?? '');
    $nivel_acesso = trim($_POST['nivel_acesso'] ?? '');
    $ativo       = isset($_POST['ativo']) ? 1 : 0;

    if (empty($nome) || empty($email) || empty($nivel_acesso)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        $senha_hash = password_hash('1q2w3e4r5t', PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare(
            "INSERT INTO usuarios (nome, email, senha, nivel_acesso, ativo, precisa_trocar_senha) VALUES (?, ?, ?, ?, ?, 1)"
        );
        $stmt->bind_param('ssssi', $nome, $email, $senha_hash, $nivel_acesso, $ativo);
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = 'Usuário cadastrado com sucesso! Senha padrão: 1q2w3e4r5t';
            header("Location: user_menu.php");
            exit();
        } else {
            $erro = 'Erro ao cadastrar usuário. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário</title>
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
                <a href="/centro_de_custos/settings/user_menu.php">Usuários</a>
                <span>/</span>
                <span class="current">Novo</span>
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
                    <h1 class="page-title"><strong>Novo</strong> Usuário</h1>
                </div>
                <a href="user_menu.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width:560px;margin:0 auto;">
                <div class="card-header">
                    <span class="card-header-title"><i class="fas fa-user-plus"></i> Dados do Usuário</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Nome <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="nome" required
                                   value="<?= isset($nome) ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">E-mail <span class="form-required">*</span></label>
                            <input type="email" class="form-control" name="email" required
                                   value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nível de Acesso <span class="form-required">*</span></label>
                            <select class="form-control" name="nivel_acesso" required>
                                <option value="">Selecione...</option>
                                <option value="ADMIN"   <?= (isset($nivel_acesso) && $nivel_acesso === 'ADMIN')   ? 'selected' : '' ?>>Admin</option>
                                <option value="USUARIO" <?= (isset($nivel_acesso) && $nivel_acesso === 'USUARIO') ? 'selected' : '' ?>>Usuário</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                <input type="checkbox" name="ativo" value="1" checked
                                       style="width:16px;height:16px;accent-color:var(--accent);">
                                <span class="form-label" style="margin:0;">Usuário ativo</span>
                            </label>
                            <span class="form-hint">A senha padrão será: <strong>1q2w3e4r5t</strong></span>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Cadastrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
