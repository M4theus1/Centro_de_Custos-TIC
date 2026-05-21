<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes   = explode(' ', trim($nome_usuario));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');

    if (empty($nome)) {
        $erro = 'O campo nome é obrigatório.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO cidades (nome) VALUES (?)");
        $stmt->bind_param('s', $nome);
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = 'Cidade cadastrada com sucesso!';
            header("Location: city_menu.php");
            exit();
        } else {
            $erro = 'Erro ao cadastrar cidade: ' . $mysqli->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Cidade</title>
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
                <a href="/centro_de_custos/settings/city_menu.php">Cidades</a>
                <span>/</span>
                <span class="current">Nova</span>
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
                    <h1 class="page-title"><strong>Nova</strong> Cidade</h1>
                </div>
                <a href="city_menu.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width:480px;margin:0 auto;">
                <div class="card-header">
                    <span class="card-header-title"><i class="fas fa-city"></i> Dados da Cidade</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Nome da Cidade <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="nome" required
                                   value="<?= isset($nome) ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : '' ?>">
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
