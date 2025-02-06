<?php
include(__DIR__ . '/../config/config.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID do setor não fornecido.');
}

$id = intval($_GET['id']);

// Busca o setor atual
$sql_code = "SELECT * FROM setores WHERE id = $id";
$result = $mysqli->query($sql_code);

if ($result->num_rows == 0) {
    die('Setor não encontrado.');
}

$setor = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);

    if (!empty($nome)) {
        $stmt = $mysqli->prepare("UPDATE setores SET nome = ? WHERE id = ?");
        $stmt->bind_param("si", $nome, $id);

        if ($stmt->execute()) {
            header('Location: setor_read.php');
            exit;
        } else {
            $error = 'Erro ao atualizar setor: ' . $mysqli->error;
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
    <title>Editar Setor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Setor</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="nome">Nome do Setor</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?php echo htmlspecialchars($setor['nome']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="sector_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>

<?php $mysqli->close(); ?>
