<?php
include(__DIR__ . '/../config/config.php');

// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID da cidade não fornecido.');
}

$id = intval($_GET['id']);

// Busca a cidade pelo ID para exibir os detalhes antes da exclusão
$sql_code = "SELECT * FROM cidades WHERE id = $id";
$result = $mysqli->query($sql_code);

if ($result->num_rows == 0) {
    die('Cidade não encontrada.');
}

$cidade = $result->fetch_assoc();

// Desativa a cidade se a confirmação for enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_query = "UPDATE cidades SET ATIVO = 0 WHERE id = $id";

    if ($mysqli->query($delete_query)) {
        header('Location: city_menu.php');
        exit;
    } else {
        $error = 'Erro ao desativar a cidade: ' . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desativar Cidade</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Desativar Cidade</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <p>Tem certeza de que deseja desativar a cidade <strong><?php echo htmlspecialchars($cidade['nome']); ?></strong>?</p>
        </div>

        <form action="" method="POST">
            <button type="submit" class="btn btn-danger">Confirmar Desativação</button>
            <a href="city_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>
