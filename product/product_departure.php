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
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/centro_de_custos/dashboard/painel.php">Centro de Custos TIC</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Produtos
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="/centro_de_custos/product/product_stock.php">Estoque</a>       
                        <a class="dropdown-item" href="/centro_de_custos/product/product_menu.php">Produto</a>
                        <a class="dropdown-item" href="/centro_de_custos/product/product_entry.php">Entrada</a> 
                        <a class="dropdown-item" href="/centro_de_custos/product/product_departure.php">Saída</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/centro_de_custos/settings/supplier_menu.php">Fornecedores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/centro_de_custos/settings/user_menu.php">Usuários</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Configurações
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="/centro_de_custos/settings/enterprise_menu.php">Empresas</a>
                        <a class="dropdown-item" href="/centro_de_custos/settings/state_menu.php">Estados</a>
                        <a class="dropdown-item" href="/centro_de_custos/settings/city_menu.php">Cidades</a>
                        <a class="dropdown-item" href="/centro_de_custos/settings/sector_menu.php">Setores</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saída de Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
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
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-sm-12">
            <div class="form-container">
                <h1 class="form-title text-center">Saída de Produto</h1>
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
<script>
    // Validação de campos obrigatórios antes do envio
    $(document).ready(function () {
        $('form').on('submit', function (e) {
            // Verifica se todos os campos obrigatórios estão preenchidos
            let camposObrigatorios = [
                '#id_empresa', 
                '#id_produto', 
                '#id_setor', 
                '#responsavel', 
                '#quantidade', 
                '#data_saida', 
                '#numero_ticket', 
                '#id_cidade', 
                '#id_estado'
            ];

            let vazio = false;

            camposObrigatorios.forEach(function (campo) {
                const valor = $(campo).val();
                if (!valor || valor.trim() === '') {
                    vazio = true;
                    $(campo).addClass('is-invalid'); // Adiciona estilo de erro no campo
                } else {
                    $(campo).removeClass('is-invalid'); // Remove estilo de erro, se houver
                }
            });

            if (vazio) {
                e.preventDefault(); // Impede o envio do formulário
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    });
</script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>