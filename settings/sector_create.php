<?php
include(__DIR__ . '/../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);

    if (!empty($nome)) {
        $stmt = $mysqli->prepare("INSERT INTO setores (nome) VALUES (?)");
        $stmt->bind_param("s", $nome);

        if ($stmt->execute()) {
            // Redireciona para sector_menu.php após salvar
            header('Location: sector_menu.php');
            exit;
        } else {
            $error = 'Erro ao adicionar setor: ' . $mysqli->error;
        }
    } else {
        $error = 'O campo nome é obrigatório.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Setor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Adicionar Novo Setor</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="nome">Nome do Setor</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="sector_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>

<?php $mysqli->close(); ?>
