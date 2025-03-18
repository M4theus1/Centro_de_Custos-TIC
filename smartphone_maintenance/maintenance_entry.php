<?php
session_start();
include(__DIR__ . '/../config/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$dados = ['descricao_servico' => '', 'imei' => '', 'data_servico' => date('Y-m-d'), 'valor' => '', 'nota_fiscal' => ''];

if ($id) {
    $result = $mysqli->query("SELECT * FROM manutencao_celular WHERE id = $id");
    $dados = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Nova' ?> Manutenção</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include('C:/xampp/htdocs/centro_de_custos/sidebar.php'); ?>

    <div class="container mt-5">
        <h1 class="text-center"><?= $id ? 'Editar' : 'Nova' ?> Manutenção</h1>
        <form method="POST" action="maintenance_entry_form.php">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="mb-3">
                <label class="form-label">Serviço Feito</label>
                <input type="text" class="form-control" name="descricao_servico" value="<?= $dados['descricao_servico'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">IMEI</label>
                <input type="text" class="form-control" name="imei" value="<?= $dados['imei'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Data</label>
                <input type="date" class="form-control" name="data_servico" value="<?= $dados['data_servico'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Valor</label>
                <input type="text" class="form-control" name="valor" value="<?= $dados['valor'] ?>" required>
            </div>

            <form method="POST" action="manutencao_entry_form.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nota Fiscal (PDF ou Imagem)</label>
                    <input type="file" class="form-control" name="nota_fiscal" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if (!empty($dados['nota_fiscal'])): ?>
                        <p>Arquivo atual: <a href="<?= $dados['nota_fiscal'] ?>" target="_blank">Ver Nota Fiscal</a></p>
                    <?php endif; ?>
                </div>
            </form>

            <div class="d-flex justify-content-between">
                <a href="maintenance_menu.php" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</body>
</html>
