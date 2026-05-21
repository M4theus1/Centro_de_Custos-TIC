<?php
session_start();
require(__DIR__ . '/../config/config.php');

// Iniciais do nome do usuário para o avatar
$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Entrada de Produto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Sistema -->
    <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background: #f8f9fa; }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,.1);
        }
        .select2-container .select2-selection--single {
            height: 38px;
        }
        .select2-selection__rendered {
            line-height: 36px !important;
        }
        .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
</head>
<body>

<?php include(__DIR__ . '/../sidebar.php'); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="form-container">
                <h3 class="mb-4 text-center">📦 Entrada de Produto</h3>

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form method="POST" action="product_entry_handler.php" enctype="multipart/form-data">

                    <!-- Empresa -->
                    <div class="mb-3">
                        <label class="form-label">Empresa</label>
                        <select name="id_empresa" id="empresa" class="form-select" required></select>
                    </div>

                    <!-- Produto -->
                    <div class="mb-3">
                        <label class="form-label">Produto</label>
                        <select name="id_produto" id="produto" class="form-select" required></select>
                    </div>

                    <!-- Fornecedor -->
                    <div class="mb-3">
                        <label class="form-label">Fornecedor</label>
                        <select name="id_fornecedor" id="fornecedor" class="form-select" required></select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantidade" id="quantidade" class="form-control" min="1" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valor Unitário</label>
                            <input type="text" name="valor_unitario" id="valor_unitario" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Frete</label>
                            <input type="text" name="frete" id="frete" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valor Total</label>
                            <input type="text" name="valor_total" id="valor_total" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data de Entrada</label>
                        <input type="date" name="data_entrada" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nota Fiscal</label>
                        <input type="file" name="nf" class="form-control" accept=".pdf,.docx">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea name="observacao" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Voltar
                        </a>
                        <button type="button" class="btn btn-primary" id="abrirModalEntrada">
                            <i class="fa fa-check"></i> Registrar Entrada
                        </button>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="entradaModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirmar Entrada</h5>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><b>Empresa:</b> <span id="m_empresa"></span></p>
                                    <p><b>Produto:</b> <span id="m_produto"></span></p>
                                    <p><b>Fornecedor:</b> <span id="m_fornecedor"></span></p>
                                    <p><b>Quantidade:</b> <span id="m_quantidade"></span></p>
                                    <p><b>Valor Total:</b> <span id="m_total"></span></p>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Confirmar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function initSelect(selector, url, placeholder) {
    $(selector).select2({
        theme: 'bootstrap-5',
        placeholder: placeholder,
        allowClear: true,
        ajax: {
            url: url,
            dataType: 'json',
            delay: 300,
            data: params => ({ search: params.term }),
            processResults: data => ({
                results: data.map(item => ({ id: item.id, text: item.nome }))
            })
        }
    });
}

initSelect('#empresa', 'ajax/buscar_empresas.php', 'Buscar empresa');
initSelect('#produto', 'ajax/buscar_produtos.php', 'Buscar produto');
initSelect('#fornecedor', 'ajax/buscar_fornecedores.php', 'Buscar fornecedor');

$('#quantidade, #valor_unitario, #frete').on('input', function () {
    const q = parseFloat($('#quantidade').val()) || 0;
    const v = parseFloat($('#valor_unitario').val().replace(',', '.')) || 0;
    const f = parseFloat($('#frete').val().replace(',', '.')) || 0;
    $('#valor_total').val(((q * v) + f).toFixed(2).replace('.', ','));
});

$('#abrirModalEntrada').on('click', function () {
    if (!$('#empresa').val() || !$('#produto').val() || !$('#fornecedor').val()) {
        alert('Preencha empresa, produto e fornecedor.');
        return;
    }
    $('#m_empresa').text($('#empresa option:selected').text());
    $('#m_produto').text($('#produto option:selected').text());
    $('#m_fornecedor').text($('#fornecedor option:selected').text());
    $('#m_quantidade').text($('#quantidade').val());
    $('#m_total').text($('#valor_total').val());
    new bootstrap.Modal(document.getElementById('entradaModal')).show();
});
</script>

</body>
</html>
