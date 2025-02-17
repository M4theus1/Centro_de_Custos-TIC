<?php
include(__DIR__ . '/../config/config.php');
session_start();

// Obter e validar o ID do produto
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'texto' => 'ID inválido ou produto não encontrado.',
    ];
    header("Location: product_menu.php");
    exit();
}

// Inicializar variáveis
$erro = null;

try {
    // Buscar produto pelo ID
    $sql = "SELECT * FROM produtos WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['mensagem'] = [
            'tipo' => 'danger',
            'texto' => 'Produto não encontrado.',
        ];
        header("Location: product_menu.php");
        exit();
    }

    $produto = $result->fetch_assoc();

    // Atualizar produto ao enviar o formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $marca = filter_input(INPUT_POST, 'marca', FILTER_SANITIZE_STRING);

        if ($nome && $marca) {
            $sql_update = "UPDATE produtos SET nome = ?, marca = ? WHERE id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param('ssi', $nome, $marca, $id);

            if ($stmt_update->execute()) {
                $_SESSION['mensagem'] = [
                    'tipo' => 'success',
                    'texto' => 'Produto atualizado com sucesso!',
                ];
                header("Location: product_menu.php");
                exit();
            } else {
                $erro = "Erro ao atualizar produto. Por favor, tente novamente.";
            }
        } else {
            $erro = "Por favor, preencha todos os campos obrigatórios.";
        }
    }
} catch (Exception $e) {
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'texto' => 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.',
    ];
    header("Location: product_menu.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #ffffff;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include('C:/xampp/htdocs/centro_de_custos/sidebar.php'); ?>

    <!-- Conteúdo principal -->
    <div class="main-content">
        <div class="container mt-5">
            <h2 class="mb-4">Editar Produto</h2>

            <!-- Exibir mensagem de erro, se houver -->
            <?php if ($erro): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <!-- Formulário de edição -->
            <form method="POST">
                <div class="form-group">
                    <label for="nome">Produto:</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="nome" 
                        name="nome" 
                        value="<?php echo htmlspecialchars($produto['nome']); ?>" 
                        required>
                </div>
                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="marca" 
                        name="marca" 
                        value="<?php echo htmlspecialchars($produto['marca']); ?>" 
                        required>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="product_menu.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
