<?php 
include(__DIR__ . '/../config/config.php');

// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('ID da empresa não fornecido.');
}

$id = intval($_GET['id']);

// Busca a empresa pelo ID
$sql_code = "SELECT * FROM empresas WHERE id = $id";
$result = $mysqli->query($sql_code);

if ($result->num_rows == 0) {
    die('Empresa não encontrada.');
}

$empresa = $result->fetch_assoc();

// Atualiza os dados da empresa se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $mysqli->real_escape_string($_POST['nome']);
    $cnpj = preg_replace('/\D/', '', $_POST['cnpj']); // Remove a máscara do CNPJ

    if (empty($nome) || empty($cnpj)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $update_query = "UPDATE empresas SET nome = '$nome', cnpj_empresa = '$cnpj' WHERE id = $id";

        if ($mysqli->query($update_query)) {
            header('Location: enterprise.php');
            exit;
        } else {
            $error = 'Erro ao atualizar a empresa: ' . $mysqli->error;
        }
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
    <title>Editar Empresa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Empresa</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($empresa['nome']); ?>" required>
            </div>

            <div class="form-group">
                <label for="cnpj">CNPJ</label>
                <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?php echo formatarCNPJ($empresa['cnpj_empresa']); ?>" required maxlength="18">
            </div>

            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="enterprise_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            $('#cnpj').on('input', function () {
                let cnpj = $(this).val().replace(/\D/g, '');
                if (cnpj.length > 14) {
                    cnpj = cnpj.substring(0, 14);
                }

                cnpj = cnpj.replace(/^(\d{2})(\d)/, '$1.$2');
                cnpj = cnpj.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                cnpj = cnpj.replace(/\.(\d{3})(\d)/, '.$1/$2');
                cnpj = cnpj.replace(/(\d{4})(\d)/, '$1-$2');

                $(this).val(cnpj);
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $mysqli->close(); ?>
