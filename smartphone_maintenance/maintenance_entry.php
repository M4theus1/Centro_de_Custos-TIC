<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Consulta corrigida para obter os valores ENUM
$query_tipo_custo = $mysqli->query("
    SELECT COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'manutencao_celular'
    AND COLUMN_NAME = 'tipo_custo'
");

if (!$query_tipo_custo) {
    die("Erro ao consultar tipos de custo: " . $mysqli->error);
}

$row = $query_tipo_custo->fetch_assoc();
$enum_values = str_replace(["enum(", ")", "'"], "", $row['COLUMN_TYPE']); // Limpa a string
$tipos = explode(",", $enum_values);

// Verifica e cria a pasta de uploads se não existir
$uploadDir = __DIR__ . '/uploads';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die("Falha ao criar diretório de uploads");
    }
} elseif (!is_writable($uploadDir)) {
    die("Diretório de uploads não tem permissão de escrita");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada de Manutenção</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            margin-top: 20px;
        }
        .form-title {
            font-weight: bold;
            color: #343a40;
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            text-align: center;
        }
        .form-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include(__DIR__ . '/../sidebar.php'); ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="form-container">
                    <h1 class="form-title">Entrada de Manutenção</h1>

                    <!-- Exibe mensagens de sucesso ou erro -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST" action="maintenance_entry_form.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="descricao_servico" class="form-label">Descrição do Serviço</label>
                            <input type="text" class="form-control" name="descricao_servico" required>
                        </div>
                        <div class="mb-3">
                            <label for="imei" class="form-label">IMEI do Aparelho</label>
                            <input type="text" class="form-control" name="imei" required>
                        </div>
                        <div class="mb-3">
                            <label for="responsavel" class="form-label">Responsável</label>
                            <input type="text" class="form-control" name="responsavel" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="data_servico" class="form-label">Data do Serviço</label>
                                    <input type="date" class="form-control" name="data_servico" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valor" class="form-label">Valor</label>
                                    <input type="text" class="form-control" name="valor" id="valor" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_custo" class="form-label required">Tipo de Custo</label>
                            <select class="form-select" name="tipo_custo" required>
                                <option value="">Selecione o Tipo de Custo</option>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option value="<?= htmlspecialchars(trim($tipo)) ?>"><?= htmlspecialchars(trim($tipo)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nota_fiscal" class="form-label">Nota Fiscal</label>
                            <input type="file" class="form-control" name="nota_fiscal" accept=".pdf,.jpg,.png" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">Voltar</a>
                            <button type="submit" class="btn btn-primary">Registrar Manutenção</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para formatação de valores -->
    <script>
        $(document).ready(function () {
            $('#valor').on('input', function () {
                let valor = $(this).val().replace(',', '.');
                $(this).val(valor);
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>