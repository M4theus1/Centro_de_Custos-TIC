<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes   = explode(' ', trim($nome_usuario));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: product_menu.php");
    exit();
}

$erro = '';

$stmt = $mysqli->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();

if (!$produto) {
    header("Location: product_menu.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome']  ?? '');
    $marca = trim($_POST['marca'] ?? '');

    if (empty($nome)) {
        $erro = 'Por favor, preencha o nome do produto.';
    } else {
        $stmt_up = $mysqli->prepare("UPDATE produtos SET nome = ?, marca = ? WHERE id = ?");
        $stmt_up->bind_param('ssi', $nome, $marca, $id);
        if ($stmt_up->execute()) {
            $_SESSION['mensagem'] = 'Produto atualizado com sucesso!';
            header("Location: product_menu.php");
            exit();
        } else {
            $erro = 'Erro ao atualizar produto. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
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
                <a href="/centro_de_custos/product/product_menu.php">Produtos</a>
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
                    <span class="page-eyebrow">Produtos</span>
                    <h1 class="page-title"><strong>Editar</strong> Produto</h1>
                </div>
                <a href="product_menu.php" class="btn btn-secondary">
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
                    <span class="card-header-title"><i class="fas fa-edit"></i> Dados do Produto</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Nome do Produto <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="nome" required
                                   value="<?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" name="marca"
                                   value="<?= htmlspecialchars($produto['marca'], ENT_QUOTES, 'UTF-8') ?>">
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
</body>
</html>
