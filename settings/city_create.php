<?php
include(__DIR__ . '/../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $mysqli->real_escape_string($_POST['nome']);

    $sql = "INSERT INTO cidades (nome) VALUES ('$nome')";
    if ($mysqli->query($sql)) {
        header("Location: city_menu.php");
        exit();
    } else {
        $erro = "Erro ao adicionar cidade: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Cidade</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Adicionar Nova Cidade</h2>
        <?php if (isset($erro)) : ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome da Cidade</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="city_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>
