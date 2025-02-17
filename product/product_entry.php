<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Verifica e cria a pasta de uploads se não existir
$uploadDir = __DIR__ . '/uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Carrega os dados necessários para os dropdowns
$query_empresas = $mysqli->query("SELECT id, nome FROM empresas WHERE ativo=1");
$query_produtos = $mysqli->query("SELECT id, nome FROM produtos");
$query_fornecedores = $mysqli->query("SELECT id, nome FROM fornecedores WHERE ativo=1");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Entrada de Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
        }
        .sidebar {
            height: 100vh;
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
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <!-- Conteúdo principal -->
    <div class="main-content">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-sm-12">
                    <div class="form-container bg-white p-4 rounded shadow">
                        <h1 class="text-center border-bottom pb-3">Cadastro de Entrada de Produto</h1>

                        <!-- Exibe mensagens de sucesso ou erro -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?= $_SESSION['success'] ?>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error'] ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="POST" action="product_entry_handler.php" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Empresa</label>
                                <select class="form-select" name="id_empresa" required>
                                    <option value="">Selecione a empresa</option>
                                    <?php while ($empresa = $query_empresas->fetch_assoc()): ?>
                                        <option value="<?= $empresa['id'] ?>"><?= $empresa['nome'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Produto</label>
                                <select class="form-select" name="id_produto" required>
                                    <option value="">Selecione o produto</option>
                                    <?php while ($produto = $query_produtos->fetch_assoc()): ?>
                                        <option value="<?= $produto['id'] ?>"><?= $produto['nome'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fornecedor</label>
                                <select class="form-select" name="id_fornecedor" required>
                                    <option value="">Selecione o fornecedor</option>
                                    <?php while ($fornecedor = $query_fornecedores->fetch_assoc()): ?>
                                        <option value="<?= $fornecedor['id'] ?>"><?= $fornecedor['nome'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">Voltar</a>
                                <button type="submit" class="btn btn-primary">Registrar Entrada</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
