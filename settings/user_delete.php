<?php 
include(__DIR__ . '/../config/config.php');

// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID do usuário não fornecido.');
}

$id = intval($_GET['id']);

// Busca o usuário pelo ID para exibir os detalhes antes da desativação
$sql_code = "SELECT * FROM usuarios WHERE id = $id";
$result = $mysqli->query($sql_code);

if ($result->num_rows == 0) {
    die('Usuário não encontrado.');
}

$usuario = $result->fetch_assoc();

// Desativa o usuário se a confirmação for enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update_query = "UPDATE usuarios SET ativo = 0 WHERE id = $id";

    if ($mysqli->query($update_query)) {
        header('Location: user_menu.php');
        exit;
    } else {
        $error = 'Erro ao desativar o usuário: ' . $mysqli->error;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desativar Usuário</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Desativar Usuário</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <p>Tem certeza de que deseja desativar o usuário <strong><?php echo htmlspecialchars($usuario['nome']); ?></strong>?</p>
        </div>

        <form action="" method="POST">
            <button type="submit" class="btn btn-danger">Confirmar Desativação</button>
            <a href="user_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>
