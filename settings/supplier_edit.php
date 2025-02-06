<?php
include(__DIR__ . '/../config/config.php');

$id = $_GET['id'];
$sql = "SELECT * FROM fornecedores WHERE id = $id";
$result = $mysqli->query($sql);
$fornecedor = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $mysqli->real_escape_string($_POST['nome']);

    $sql_update = "UPDATE fornecedores SET nome = '$nome' WHERE id = $id";
    if ($mysqli->query($sql_update)) {
        header("Location: supplier_menu.php");
        exit();
    } else {
        $erro = "Erro ao atualizar fornecedor: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Fornecedor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Editar Fornecedor</h2>
        <?php if (isset($erro)) : ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="nome">Nome do Fornecedor</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($fornecedor['nome']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="supplier_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>
