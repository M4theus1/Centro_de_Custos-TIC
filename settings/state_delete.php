<?php 
include(__DIR__ . '/../config/config.php');

// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID do estado não fornecido.');
}

$id = intval($_GET['id']);

// Busca o estado pelo ID para exibir os detalhes antes da exclusão
$sql_code = "SELECT * FROM estados WHERE id = $id";
$result = $mysqli->query($sql_code);

if ($result->num_rows == 0) {
    die('Estado não encontrado.');
}

$estado = $result->fetch_assoc();

// Desativa o estado se a confirmação for enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_query = "UPDATE estados SET ATIVO = 0 WHERE id = $id";

    if ($mysqli->query($delete_query)) {
        header('Location: state_menu.php');
        exit;
    } else {
        $error = 'Erro ao desativar o estado: ' . $mysqli->error;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desativar Estado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Desativar Estado</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <p>Tem certeza de que deseja excluir o estado <strong><?php echo htmlspecialchars($estado['nome']); ?></strong>?</p>
        </div>

        <form action="" method="POST">
            <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
            <a href="state_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>
