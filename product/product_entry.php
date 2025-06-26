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
                    <h1 class="form-title text-center">Cadastro de Entrada de Produto</h1>

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
                            <label for="id_empresa" class="form-label">Empresa</label>
                            <select class="form-select" name="id_empresa" required>
                                <option value="">Selecione a empresa</option>
                                <?php while ($empresa = $query_empresas->fetch_assoc()): ?>
                                    <option value="<?= $empresa['id'] ?>"><?= $empresa['nome'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_produto" class="form-label">Produto</label>
                            <select class="form-select" name="id_produto" required>
                                <option value="">Selecione o produto</option>
                                <?php while ($produto = $query_produtos->fetch_assoc()): ?>
                                    <option value="<?= $produto['id'] ?>"><?= $produto['nome'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_fornecedor" class="form-label">Fornecedor</label>
                            <select class="form-select" name="id_fornecedor" required>
                                <option value="">Selecione o fornecedor</option>
                                <?php while ($fornecedor = $query_fornecedores->fetch_assoc()): ?>
                                    <option value="<?= $fornecedor['id'] ?>"><?= $fornecedor['nome'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantidade" class="form-label">Quantidade</label>
                                    <input type="number" class="form-control" name="quantidade" id="quantidade" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valor_unitario" class="form-label">Valor Unitário</label>
                                    <input type="text" class="form-control" name="valor_unitario" id="valor_unitario" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="frete" class="form-label">Frete</label>
                                    <input type="text" class="form-control" name="frete" id="frete">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valor_total" class="form-label">Valor Total</label>
                                    <input type="text" class="form-control" name="valor_total" id="valor_total" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="data_entrada" class="form-label">Data de Entrada</label>
                            <input type="date" class="form-control" name="data_entrada" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="nf" class="form-label">Nota Fiscal</label>
                            <input type="file" class="form-control" name="nf" id="nf" accept=".pdf,.docx">
                        </div>
                        <div class="mb-3">
                            <label for="observacao" class="form-label">Observações</label>
                            <textarea class="form-control" name="observacao" rows="3"></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">Voltar</a>
                            <button type="button" class="btn btn-primary" id="abrirModalEntrada">Registrar Entrada</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#quantidade, #valor_unitario, #frete').on('input', function () {
                const quantidade = parseFloat($('#quantidade').val().replace(',', '.')) || 0;
                const valorUnitario = parseFloat($('#valor_unitario').val().replace(',', '.')) || 0;
                const frete = parseFloat($('#frete').val().replace(',', '.')) || 0;
                const valorTotal = (quantidade * valorUnitario) + frete;
                $('#valor_total').val(valorTotal.toFixed(2).replace('.', ','));
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
    $('#abrirModalEntrada').on('click', function () {
        // Preenche os campos do modal de entrada com os valores do formulário
        $('#modal_entrada_empresa').text($('select[name="id_empresa"] option:selected').text());
        $('#modal_entrada_produto').text($('select[name="id_produto"] option:selected').text());
        $('#modal_entrada_fornecedor').text($('select[name="id_fornecedor"] option:selected').text());
        $('#modal_entrada_quantidade').text($('#quantidade').val());
        $('#modal_entrada_valor_unitario').text($('#valor_unitario').val());
        $('#modal_entrada_frete').text($('#frete').val());
        $('#modal_entrada_valor_total').text($('#valor_total').val());
        $('#modal_entrada_data').text($('input[name="data_entrada"]').val());
        $('#modal_entrada_observacao').text($('textarea[name="observacao"]').val() || 'Sem observações');

        // Exibe o modal
        $('#entradaModal').modal('show');
    });

    // Se confirmado, submete o formulário
    $('#confirmEntrada').on('click', function () {
        $('form').submit();
    });
});

    </script>

    <!-- Modal de Confirmação de Entrada -->
<div class="modal fade" id="entradaModal" tabindex="-1" aria-labelledby="entradaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="entradaModalLabel">Confirmação da Entrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <h5 class="text-center">Entrada de Produto</h5>
                <hr>
                <p><strong>Empresa:</strong> <span id="modal_entrada_empresa"></span></p>
                <p><strong>Produto:</strong> <span id="modal_entrada_produto"></span></p>
                <p><strong>Fornecedor:</strong> <span id="modal_entrada_fornecedor"></span></p>
                <p><strong>Quantidade:</strong> <span id="modal_entrada_quantidade"></span></p>
                <p><strong>Valor Unitário:</strong> <span id="modal_entrada_valor_unitario"></span></p>
                <p><strong>Frete:</strong> <span id="modal_entrada_frete"></span></p>
                <p><strong>Valor Total:</strong> <span id="modal_entrada_valor_total"></span></p>
                <p><strong>Data de Entrada:</strong> <span id="modal_entrada_data"></span></p>
                <p><strong>Observações:</strong> <span id="modal_entrada_observacao"></span></p>
                <hr>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmEntrada">Confirmar e Registrar</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
