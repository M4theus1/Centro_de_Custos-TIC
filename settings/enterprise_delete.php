<?php 
include(__DIR__ . '/../config/config.php');

// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID da empresa não fornecido.');
}

$id = intval($_GET['id']);

// Busca a empresa pelo ID para exibir os detalhes antes da exclusão
$sql_code = "SELECT * FROM empresas WHERE id = $id";
$result = $mysqli->query($sql_code);

if ($result->num_rows == 0) {
    die('Empresa não encontrada.');
}

$empresa = $result->fetch_assoc();

// Exclui a empresa se a confirmação for enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_query = "UPDATE empresas SET ATIVO = 0 WHERE id = $id";

    if ($mysqli->query($delete_query)) {
        header('Location: enterprise.php');
        exit;
    } else {
        $error = 'Erro ao excluir a empresa: ' . $mysqli->error;
    }
}

/**
 * Função para formatar o CNPJ.
 * Exemplo: 12345678000199 -> 12.345.678/0001-99
 */
function formatarCNPJ($cnpj) {
    return preg_replace(
        "/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/",
        "$1.$2.$3/$4-$5",
        $cnpj
    );
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desativar Empresa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Desativar Empresa</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="alert alert-warning">
            <p>Tem certeza de que deseja excluir a empresa <strong><?php echo htmlspecialchars($empresa['nome']); ?></strong> com CNPJ <strong><?php echo formatarCNPJ($empresa['cnpj_empresa']); ?></strong>?</p>
        </div>

        <form action="" method="POST">
            <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
            <a href="enterprise.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>
