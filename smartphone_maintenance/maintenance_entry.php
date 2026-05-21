<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$query_tipo_custo = $mysqli->query("
    SELECT COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'manutencao_celular'
    AND COLUMN_NAME = 'tipo_custo'
");

if (!$query_tipo_custo) {
    die("Erro ao consultar tipos de custo: " . $mysqli->error);
}

$row = $query_tipo_custo->fetch_assoc();
$enum_values = str_replace(["enum(", ")", "'"], "", $row['COLUMN_TYPE']);
$tipos = explode(",", $enum_values);

$uploadDir = __DIR__ . '/uploads';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die("Falha ao criar diretório de uploads");
    }
} elseif (!is_writable($uploadDir)) {
    die("Diretório de uploads não tem permissão de escrita");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada de Manutenção</title>
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
                <span>Manutenção</span>
                <span>/</span>
                <span class="current">Entrada</span>
            </nav>
            <div class="topbar-right">
                <span class="topbar-username"><?= htmlspecialchars($primeiro_nome, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="topbar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </header>

        <div class="content">
            <div class="page-header">
                <div>
                    <span class="page-eyebrow">Manutenção</span>
                    <h1 class="page-title"><strong>Entrada de</strong> Manutenção</h1>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card" style="max-width:760px;">
                <div class="card-body">
                    <form method="POST" action="maintenance_entry_form.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">Descrição do Serviço <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="descricao_servico" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">IMEI do Aparelho <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="imei" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Responsável <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="responsavel" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Data do Serviço <span class="form-required">*</span></label>
                                <input type="date" class="form-control" name="data_servico" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Valor <span class="form-required">*</span></label>
                                <input type="text" class="form-control" name="valor" id="valor" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tipo de Custo <span class="form-required">*</span></label>
                            <select class="form-control" name="tipo_custo" required>
                                <option value="">Selecione o Tipo de Custo</option>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option value="<?= htmlspecialchars(trim($tipo)) ?>"><?= htmlspecialchars(trim($tipo)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nota Fiscal <span class="form-required">*</span></label>
                            <input type="file" class="form-control" name="nota_fiscal" accept=".pdf,.jpg,.png" required>
                        </div>

                        <div class="form-footer">
                            <div class="form-footer-left">
                                <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">Voltar</a>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Registrar Manutenção
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('valor').addEventListener('input', function () {
            this.value = this.value.replace(',', '.');
        });
    });
    </script>
</body>
</html>
