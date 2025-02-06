<?php
include(__DIR__ . '/../config/config.php');

$erro = '';
$sucesso = '';

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cnpj_empresa = trim($_POST['cnpj_empresa']);

    // Validação básica
    if (empty($nome) || empty($cnpj_empresa)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        // Preparar a query de inserção
        $stmt = $mysqli->prepare("INSERT INTO empresas (nome, cnpj_empresa) VALUES (?, ?)");
        $stmt->bind_param("ss", $nome, $cnpj_empresa);

        if ($stmt->execute()) {
            $sucesso = 'Empresa adicionada com sucesso!';
            $nome = '';
            $cnpj_empresa = '';
        } else {
            $erro = 'Erro ao adicionar empresa: ' . $stmt->error;
        }

        $stmt->close();
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Empresa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Adicionar Nova Empresa</h2>

        <?php if ($erro): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <form action="enterprise_create.php" method="POST" id="empresaForm">
            <div class="form-group">
                <label for="nome">Nome da Empresa</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="cnpj_empresa">CNPJ</label>
                <input type="text" name="cnpj_empresa" id="cnpj_empresa" class="form-control" value="<?php echo isset($cnpj_empresa) ? htmlspecialchars($cnpj_empresa) : ''; ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="enterprise_menu.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <!-- jQuery e Plugin de Máscara -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            // Aplica a máscara ao campo de CNPJ
            $('#cnpj_empresa').mask('00.000.000/0000-00');

            // Remove a máscara antes de enviar o formulário
            $('#empresaForm').on('submit', function() {
                let cnpj = $('#cnpj_empresa').val().replace(/\D/g, ''); // Remove tudo que não é número
                $('#cnpj_empresa').val(cnpj);
            });
        });
    </script>
</body>
</html>
