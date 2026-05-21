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
    $cnpj = preg_replace('/\D/', '', $_POST['cnpj_empresa'] ?? '');

    if (empty($nome) || empty($cnpj)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO empresas (nome, cnpj_empresa) VALUES (?, ?)");
        $stmt->bind_param('ss', $nome, $cnpj);
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = 'Empresa cadastrada com sucesso!';
            header("Location: enterprise_menu.php");
            exit();
        } else {
            $erro = 'Erro ao cadastrar empresa: ' . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Empresa</title>
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
                <a href="/centro_de_custos/settings/enterprise_menu.php">Empresas</a>
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
                    <h1 class="page-title"><strong>Nova</strong> Empresa</h1>
                </div>
                <a href="enterprise_menu.php" class="btn btn-secondary">
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
                    <span class="card-header-title"><i class="fas fa-building"></i> Dados da Empresa</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="empresaForm">
                        <div class="form-group">
                            <label class="form-label">Nome da Empresa <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="nome" required
                                   value="<?= isset($nome) ? htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CNPJ <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="cnpj_empresa" id="cnpj_empresa"
                                   placeholder="00.000.000/0000-00" required maxlength="18">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
    $(document).ready(function () {
        $('#cnpj_empresa').mask('00.000.000/0000-00');
    });
    </script>
</body>
</html>
