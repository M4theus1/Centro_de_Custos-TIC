<?php
include(__DIR__ . '/../config/config.php');

$id = $_GET['id'];
$sql = "SELECT * FROM usuarios WHERE id = $id";
$result = $mysqli->query($sql);
$usuario = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $mysqli->real_escape_string($_POST['nome']);
    $email = $mysqli->real_escape_string($_POST['email']);

    $sql_update = "UPDATE usuarios SET nome = '$nome', email = '$email' WHERE id = $id";
    if ($mysqli->query($sql_update)) {
        header("Location: user_menu.php");
        exit();
    } else {
        $erro = "Erro ao atualizar usuário: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Editar Usuário</h2>
        <?php if (isset($erro)) : ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="user_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>
