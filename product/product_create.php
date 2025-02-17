<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Processar o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $marca = filter_input(INPUT_POST, 'marca', FILTER_SANITIZE_STRING);

    if (!empty($nome)) {
        try {
            $sql = "INSERT INTO produtos (nome, marca) VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ss', $nome, $marca);

            if ($stmt->execute()) {
                $_SESSION['mensagem'] = [
                    'tipo' => 'success',
                    'texto' => 'Produto cadastrado com sucesso!',
                ];
                header("Location: product_menu.php");
                exit();
            } else {
                throw new Exception("Erro ao cadastrar produto.");
            }
        } catch (Exception $e) {
            $_SESSION['mensagem'] = [
                'tipo' => 'danger',
                'texto' => 'Erro ao cadastrar produto. Por favor, tente novamente mais tarde.',
            ];
        } finally {
            $stmt->close();
        }
    } else {
        $_SESSION['mensagem'] = [
            'tipo' => 'danger',
            'texto' => 'Por favor, preencha o nome do produto.',
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
            margin-left: 250px; /* Ajuste para evitar sobreposição */
            padding: 20px;
        }
        .navbar {
            margin-left: 250px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include('C:/xampp/htdocs/centro_de_custos/sidebar.php'); ?>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <h1>Cadastro de Produto</h1>

        <!-- Exibir mensagens de sucesso ou erro -->
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_SESSION['mensagem']['tipo']); ?>" role="alert">
                <?php echo htmlspecialchars($_SESSION['mensagem']['texto']); ?>
            </div>
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>

        <!-- Formulário de cadastro -->
        <form method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome do Produto <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="nome" 
                    name="nome" 
                    aria-required="true" 
                    required
                    value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="marca" class="form-label">Marca</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="marca" 
                    name="marca"
                    value="<?php echo isset($marca) ? htmlspecialchars($marca) : ''; ?>">
            </div>
            <div class="d-flex justify-content-between mt-4 mb-3">
                <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">Voltar ao Dashboard</a>
                <button type="submit" class="btn btn-primary">Cadastrar Produto</button>
            </div>
        </form>
    </div> <!-- Fechamento correto da div main-content -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin
