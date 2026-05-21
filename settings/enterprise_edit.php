<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes   = explode(' ', trim($nome_usuario));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: enterprise_menu.php");
    exit();
}

$erro = '';

$stmt = $mysqli->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$empresa = $stmt->get_result()->fetch_assoc();

if (!$empresa) {
    header("Location: enterprise_menu.php");
    exit();
}

function formatarCNPJ($cnpj) {
    return preg_replace("/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/", "$1.$2.$3/$4-$5", $cnpj);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');

    if (empty($nome) || empty($cnpj)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $stmt_up = $mysqli->prepare("UPDATE empresas SET nome = ?, cnpj_empresa = ? WHERE id = ?");
        $stmt_up->bind_param('ssi', $nome, $cnpj, $id);
        if ($stmt_up->execute()) {
            $_SESSION['mensagem'] = 'Empresa atualizada com sucesso!';
            header("Location: enterprise_menu.php");
            exit();
        } else {
            $erro = 'Erro ao atualizar empresa: ' . $mysqli->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empresa</title>
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
                <span class="current">Editar</span>
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
                    <h1 class="page-title"><strong>Editar</strong> Empresa</h1>
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
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Nome da Empresa <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="nome" required
                                   value="<?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CNPJ <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="cnpj" id="cnpj"
                                   maxlength="18" required
                                   value="<?= htmlspecialchars(formatarCNPJ($empresa['cnpj_empresa']), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Salvar
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
        $('#cnpj').mask('00.000.000/0000-00');
    });
    </script>
</body>
</html>
