<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes   = explode(' ', trim($nome_usuario));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome']  ?? '');
    $sigla = strtoupper(trim($_POST['sigla'] ?? ''));

    if (empty($nome) || empty($sigla)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO estados (nome, sigla) VALUES (?, ?)");
        $stmt->bind_param('ss', $nome, $sigla);
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = 'Estado cadastrado com sucesso!';
            header("Location: state_menu.php");
            exit();
        } else {
            $erro = 'Erro ao cadastrar estado: ' . $mysqli->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Estado</title>
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
                <a href="/centro_de_custos/settings/state_menu.php">Estados</a>
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
                    <h1 class="page-title"><strong>Novo</strong> Estado</h1>
                </div>
                <a href="state_menu.php" class="btn btn-secondary">
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
                    <span class="card-header-title"><i class="fas fa-map"></i> Dados do Estado</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Nome do Estado <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="nome" required
                                   value="<?= isset($nome) ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sigla <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="sigla" maxlength="2" required
                                   style="max-width:80px;text-transform:uppercase;"
                                   value="<?= isset($sigla) ? htmlspecialchars($sigla, ENT_QUOTES, 'UTF-8') : '' ?>">
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
