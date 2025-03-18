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
        .form-label.required::after {
            content: " *";
            color: red;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .hidden {
            display: none;
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
                        <!-- Transferência de Estoque -->
                        <div class="mb-3">
                            <label class="form-label required">Haverá transferência de estoque?</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transferencia_estoque" id="transferencia_sim" value="1" required>
                                    <label class="form-check-label" for="transferencia_sim">Sim</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transferencia_estoque" id="transferencia_nao" value="0" required>
                                    <label class="form-check-label" for="transferencia_nao">Não</label>
                                </div>
                            </div>
                        </div>

                        <!-- Empresa de Origem -->
                        <div class="mb-3">
                            <label for="id_empresa_origem" class="form-label required">Empresa de Origem</label>
                            <select class="form-select" name="id_empresa_origem" required>
                                <option value="">Selecione a empresa de origem</option>
                                <?php while ($empresa = $query_empresas->fetch_assoc()): ?>
                                    <option value="<?= $empresa['id'] ?>"><?= $empresa['nome'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Empresa de Destino (condicional) -->
                        <div class="mb-3 hidden" id="empresa_destino_container">
                            <label for="id_empresa_destino" class="form-label required">Empresa de Destino</label>
                            <select class="form-select" name="id_empresa_destino">
                                <option value="">Selecione a empresa de destino</option>
                                <?php $query_empresas->data_seek(0); // Reinicia o ponteiro do resultado ?>
                                <?php while ($empresa = $query_empresas->fetch_assoc()): ?>
                                    <option value="<?= $empresa['id'] ?>"><?= $empresa['nome'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Produto -->
                        <div class="mb-3">
                            <label for="id_produto" class="form-label required">Produto</label>
                            <select class="form-select" name="id_produto" required>
                                <option value="">Selecione o produto</option>
                                <?php while ($produto = $query_produtos->fetch_assoc()): ?>
                                    <option value="<?= $produto['id'] ?>"><?= $produto['nome'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Setor -->
                        <div class="mb-3">
                            <label for="id_setor" class="form-label required">Setor</label>
                            <select class="form-select" name="id_setor" required>
                                <option value="">Selecione o setor</option>
                                <?php while ($setor = $query_setores->fetch_assoc()): ?>
                                    <option value="<?= $setor['id'] ?>"><?= $setor['nome'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Responsável -->
                        <div class="mb-3">
                            <label for="responsavel" class="form-label required">Responsável</label>
                            <input type="text" class="form-control" name="responsavel" required>
                        </div>

                        <!-- Quantidade -->
                        <div class="mb-3">
                            <label for="quantidade" class="form-label required">Quantidade</label>
                            <input type="number" class="form-control" name="quantidade" id="quantidade" min="1" required>
                        </div>

                        <!-- Data de Saída -->
                        <div class="mb-3">
                            <label for="data_saida" class="form-label required">Data de Saída</label>
                            <input type="date" class="form-control" name="data_saida" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Número do Ticket -->
                        <div class="mb-3">
                            <label for="numero_ticket" class="form-label required">Número do Ticket</label>
                            <input type="text" class="form-control" name="numero_ticket" required>
                        </div>

                        <!-- Cidade -->
                        <div class="mb-3">
                            <label for="id_cidade" class="form-label required">Cidade</label>
                            <select class="form-select" name="id_cidade" required>
                                <option value="">Selecione a cidade</option>
                                <?php while ($cidade = $query_cidades->fetch_assoc()): ?>
                                    <option value="<?= $cidade['id'] ?>"><?= $cidade['nome'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label for="id_estado" class="form-label required">Estado</label>
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
                            <button type="button" class="btn btn-primary" id="submitButton">Registrar Saída</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Mostra ou oculta o campo de empresa de destino
            $('input[name="transferencia_estoque"]').on('change', function () {
                if ($('#transferencia_sim').is(':checked')) {
                    $('#empresa_destino_container').removeClass('hidden');
                    $('select[name="id_empresa_destino"]').attr('required', true);
                } else {
                    $('#empresa_destino_container').addClass('hidden');
                    $('select[name="id_empresa_destino"]').attr('required', false);
                }
            });

            // Validação dos campos obrigatórios
            $('form').on('submit', function (e) {
                let camposObrigatorios = [
                    'select[name="id_empresa_origem"]',
                    'select[name="id_produto"]',
                    'select[name="id_setor"]',
                    'input[name="responsavel"]',
                    'input[name="quantidade"]',
                    'input[name="data_saida"]',
                    'input[name="numero_ticket"]',
                    'select[name="id_cidade"]',
                    'select[name="id_estado"]'
                ];

                if ($('#transferencia_sim').is(':checked')) {
                    camposObrigatorios.push('select[name="id_empresa_destino"]');
                }

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
    <script>
    $(document).ready(function () {
        $('#submitButton').on('click', function () {
            // Coletando os valores preenchidos
            $('#modal_transferencia').text($('input[name="transferencia_estoque"]:checked').next('label').text());
            $('#modal_empresa_origem').text($('select[name="id_empresa_origem"] option:selected').text());
            $('#modal_empresa_destino').text($('select[name="id_empresa_destino"]').is(':visible') ? $('select[name="id_empresa_destino"] option:selected').text() : 'N/A');
            $('#modal_produto').text($('select[name="id_produto"] option:selected').text());
            $('#modal_setor').text($('select[name="id_setor"] option:selected').text());
            $('#modal_responsavel').text($('input[name="responsavel"]').val());
            $('#modal_quantidade').text($('input[name="quantidade"]').val());
            $('#modal_data_saida').text($('input[name="data_saida"]').val());
            $('#modal_ticket').text($('input[name="numero_ticket"]').val());
            $('#modal_cidade').text($('select[name="id_cidade"] option:selected').text());
            $('#modal_estado').text($('select[name="id_estado"] option:selected').text());
            $('#modal_observacao').text($('textarea[name="observacao"]').val() || 'Sem observações');

            // Exibir o modal
            $('#confirmModal').modal('show');
        });

        // Se confirmado, submete o formulário
        $('#confirmSubmit').on('click', function () {
            $('form').submit();
        });
    });
    </script>
    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmação da Saída</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <h5 class="text-center">Saída de Produto</h5>
                    <hr>
                    <p><strong>Transferência de Estoque:</strong> <span id="modal_transferencia"></span></p>
                    <p><strong>Empresa de Origem:</strong> <span id="modal_empresa_origem"></span></p>
                    <p><strong>Empresa de Destino:</strong> <span id="modal_empresa_destino"></span></p>
                    <p><strong>Produto:</strong> <span id="modal_produto"></span></p>
                    <p><strong>Setor:</strong> <span id="modal_setor"></span></p>
                    <p><strong>Responsável:</strong> <span id="modal_responsavel"></span></p>
                    <p><strong>Quantidade:</strong> <span id="modal_quantidade"></span></p>
                    <p><strong>Data de Saída:</strong> <span id="modal_data_saida"></span></p>
                    <p><strong>Número do Ticket:</strong> <span id="modal_ticket"></span></p>
                    <p><strong>Cidade:</strong> <span id="modal_cidade"></span></p>
                    <p><strong>Estado:</strong> <span id="modal_estado"></span></p>
                    <p><strong>Observações:</strong> <span id="modal_observacao"></span></p>
                    <hr>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirmSubmit">Confirmar e Registrar</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>