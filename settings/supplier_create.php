<?php
include(__DIR__ . '/../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $mysqli->real_escape_string($_POST['nome']);

    $sql = "INSERT INTO fornecedores (nome) VALUES ('$nome')";
    if ($mysqli->query($sql)) {
        header("Location: supplier_menu.php");
        exit();
    } else {
        $erro = "Erro ao adicionar fornecedor: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Fornecedor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Adicionar Novo Fornecedor</h2>
        <?php if (isset($erro)) : ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome do Fornecedor</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="supplier_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>
