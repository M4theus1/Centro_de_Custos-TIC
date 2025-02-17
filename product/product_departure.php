<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Carrega os dados necessários para os dropdowns
$query_empresas = $mysqli->query("SELECT id, nome FROM empresas");
$query_produtos = $mysqli->query("SELECT id, nome FROM produtos");
$query_setores = $mysqli->query("SELECT id, nome FROM setores");
$query_cidades = $mysqli->query("SELECT id, nome FROM cidades");
$query_estados = $mysqli->query("SELECT id, nome FROM estados");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saída de Produto</title>
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
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            font-weight: bold;
            color: #343a40;
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include(__DIR__ . '/../sidebar.php'); ?>

        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-sm-12">
                    <div class="form-container">
                        <h1 class="form-title text-center">Saída de Produto</h1>

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

                        <form method="POST" action="product_departure_handler.php">
                            <!-- Empresa -->
                            <div class="mb-3">
                                <label for="id_empresa" class="form-label">Empresa</label>
                                <select class="form-select" name="id_empresa" required>
                                    <option value="">Selecione a empresa</option>
                                    <?php while ($empresa = $query_empresas->fetch_assoc()): ?>
                                        <option value="<?= $empresa['id'] ?>"><?= $empresa['nome'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Produto -->
                            <div class="mb-3">
                                <label for="id_produto" class="form-label">Produto</label>
                                <select class="form-select" name="id_produto" required>
                                    <option value="">Selecione o produto</option>
                                    <?php while ($produto = $query_produtos->fetch_assoc()): ?>
                                        <option value="<?= $produto['id'] ?>"><?= $produto['nome'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Setor -->
                            <div class="mb-3">
                                <label for="id_setor" class="form-label">Setor</label>
                                <select class="form-select" name="id_setor" required>
                                    <option value="">Selecione o setor</option>
                                    <?php while ($setor = $query_setores->fetch_assoc()): ?>
                                        <option value="<?= $setor['id'] ?>"><?= $setor['nome'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Responsável -->
                            <div class="mb-3">
                                <label for="responsavel" class="form-label">Responsável</label>
                                <input type="text" class="form-control" name="responsavel" required>
                            </div>

                            <!-- Quantidade -->
                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" name="quantidade" id="quantidade" min="1" required>
                            </div>

                            <!-- Data de Saída -->
                            <div class="mb-3">
                                <label for="data_saida" class="form-label">Data de Saída</label>
                                <input type="date" class="form-control" name="data_saida" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <!-- Número do Ticket -->
                            <div class="mb-3">
                                <label for="numero_ticket" class="form-label">Número do Ticket</label>
                                <input type="text" class="form-control" name="numero_ticket" required>
                            </div>

                            <!-- Cidade -->
                            <div class="mb-3">
                                <label for="id_cidade" class="form-label">Cidade</label>
                                <select class="form-select" name="id_cidade" required>
                                    <option value="">Selecione a cidade</option>
                                    <?php while ($cidade = $query_cidades->fetch_assoc()): ?>
                                        <option value="<?= $cidade['id'] ?>"><?= $cidade['nome'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Estado -->
                            <div class="mb-3">
                                <label for="id_estado" class="form-label">Estado</label>
                                <select class="form-select" name="id_estado" required>
                                    <option value="">Selecione o estado</option>
                                    <?php while ($estado = $query_estados->fetch_assoc()): ?>
                                        <option value="<?= $estado['id'] ?>"><?= $estado['nome'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Observações -->
                            <div class="mb-3">
                                <label for="observacao" class="form-label">Observações</label>
                                <textarea class="form-control" name="observacao" rows="3"></textarea>
                            </div>

                            <!-- Botões -->
                            <div class="d-flex justify-content-between">
                                <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">Voltar</a>
                                <button type="submit" class="btn btn-primary">Registrar Saída</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('form').on('submit', function (e) {
                let camposObrigatorios = [
                    'select[name="id_empresa"]', 
                    'select[name="id_produto"]', 
                    'select[name="id_setor"]', 
                    'input[name="responsavel"]', 
                    'input[name="quantidade"]', 
                    'input[name="data_saida"]', 
                    'input[name="numero_ticket"]', 
                    'select[name="id_cidade"]', 
                    'select[name="id_estado"]'
                ];

                let vazio = false;

                camposObrigatorios.forEach(function (campo) {
                    let elemento = $(campo);
                    let valor = elemento.val();

                    if (!valor || valor === "" || valor === null) {
                        vazio = true;
                        elemento.addClass('is-invalid');
                    } else {
                        elemento.removeClass('is-invalid');
                    }
                });

                if (vazio) {
                    e.preventDefault();
                    alert('Por favor, preencha todos os campos obrigatórios.');
                }
            });
        });
    </script>
</body>
</html>