<?php
session_start();
include(__DIR__ . '/../config/config.php');

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$query_empresas = $mysqli->query("SELECT id, nome FROM empresas");
$query_produtos = $mysqli->query("SELECT id, nome FROM produtos ORDER BY nome ASC");
$query_setores  = $mysqli->query("SELECT id, nome FROM setores");
$query_cidades  = $mysqli->query("SELECT id, nome FROM cidades");
$query_estados  = $mysqli->query("SELECT id, nome FROM estados");
$tipos_custo    = ['MANUTENCAO' => 'Manutenção', 'INVESTIMENTO' => 'Investimento'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saída de Produto</title>
    <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .hidden { display: none; }

        .radio-group { display: flex; gap: 20px; align-items: center; }
        .radio-label {
            display: flex; align-items: center; gap: 6px;
            cursor: pointer; font-size: 14px; color: var(--ink-2);
        }
        .radio-label input[type="radio"] { accent-color: var(--accent); width: 15px; height: 15px; }

        #lotes_container {
            background: var(--paper-2); border: 1px solid var(--border);
            border-radius: var(--radius-sm); padding: 12px;
        }
        #lotes_container table { width: 100%; border-collapse: collapse; font-size: 13px; }
        #lotes_container thead th {
            padding: 8px 12px; font-family: var(--font-mono); font-size: 10px;
            letter-spacing: .1em; text-transform: uppercase; color: var(--ink-4);
            background: var(--paper-3); border-bottom: 1px solid var(--border-strong);
        }
        #lotes_container tbody td {
            padding: 9px 12px; border-bottom: 1px solid var(--border-subtle); color: var(--ink-2);
        }
        #lotes_container tbody tr:last-child td { border-bottom: none; }
        #lotes_container input[type="number"] {
            width: 90px; padding: 5px 8px;
            border: 1px solid var(--border-strong); border-radius: var(--radius-sm);
            background: var(--paper); color: var(--ink); font-size: 13px; outline: none;
        }
        #lotes_container input[type="number"]:focus {
            border-color: var(--accent); box-shadow: 0 0 0 3px rgba(50,104,228,.12);
        }

        .modal-table { width: 100%; border-collapse: collapse; font-size: 13.5px; margin-top: 14px; }
        .modal-table td { padding: 7px 0; border-bottom: 1px solid var(--border-subtle); vertical-align: top; }
        .modal-table td:first-child { color: var(--ink-3); width: 45%; }
        .modal-table tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main">
        <header class="topbar">
            <nav class="topbar-breadcrumb">
                <a href="/centro_de_custos/dashboard/painel.php">Início</a>
                <span>/</span>
                <a href="/centro_de_custos/product/product_menu.php">Produtos</a>
                <span>/</span>
                <span class="current">Saída</span>
            </nav>
            <div class="topbar-right">
                <span class="topbar-username"><?= htmlspecialchars($primeiro_nome, ENT_QUOTES, 'UTF-8') ?></span>
                <div class="topbar-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </header>

        <div class="content">

            <div class="page-header">
                <div>
                    <span class="page-eyebrow">Produtos</span>
                    <h1 class="page-title"><strong>Saída</strong> de Produto</h1>
                </div>
                <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card" style="max-width: 760px; margin: 0 auto;">
                <div class="card-header">
                    <span class="card-header-title"><i class="fas fa-box-open"></i> Dados da Saída</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="product_departure_handler.php" id="formSaida">

                        <div class="form-group">
                            <label class="form-label">Haverá transferência de estoque? <span class="form-required">*</span></label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="transferencia_estoque" id="transferencia_sim" value="1" required>
                                    Sim
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="transferencia_estoque" id="transferencia_nao" value="0" required>
                                    Não
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Empresa de Origem <span class="form-required">*</span></label>
                            <select class="form-control" name="id_empresa_origem" required>
                                <option value="">Selecione a empresa de origem</option>
                                <?php while ($empresa = $query_empresas->fetch_assoc()): ?>
                                    <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group hidden" id="empresa_destino_container">
                            <label class="form-label">Empresa de Destino <span class="form-required">*</span></label>
                            <select class="form-control" name="id_empresa_destino">
                                <option value="">Selecione a empresa de destino</option>
                                <?php $query_empresas->data_seek(0); ?>
                                <?php while ($empresa = $query_empresas->fetch_assoc()): ?>
                                    <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Produto <span class="form-required">*</span></label>
                            <select class="form-control" name="id_produto" required>
                                <option value="">Selecione o produto</option>
                                <?php while ($produto = $query_produtos->fetch_assoc()): ?>
                                    <option value="<?= $produto['id'] ?>"><?= htmlspecialchars($produto['nome']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Selecionar Lotes Manualmente</label>
                            <div id="lotes_container">
                                <p style="color:var(--ink-4);font-size:13px;">Selecione o produto e a empresa de origem para listar os lotes disponíveis.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Setor <span class="form-required">*</span></label>
                            <select class="form-control" name="id_setor" required>
                                <option value="">Selecione o setor</option>
                                <?php while ($setor = $query_setores->fetch_assoc()): ?>
                                    <option value="<?= $setor['id'] ?>"><?= htmlspecialchars($setor['nome']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Responsável <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="responsavel" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quantidade Total <span class="form-required">*</span></label>
                            <input type="number" class="form-control" name="quantidade" id="quantidade" min="1" required readonly style="background:var(--paper-2);cursor:not-allowed;">
                            <span class="form-hint">Preenchido automaticamente pelos lotes selecionados.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tipo de Custo <span class="form-required">*</span></label>
                            <select class="form-control" name="tipo_custo" id="tipo_custo" required>
                                <option value="">Selecione o tipo</option>
                                <?php foreach ($tipos_custo as $valor => $label): ?>
                                    <option value="<?= $valor ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Data de Saída <span class="form-required">*</span></label>
                            <input type="date" class="form-control" name="data_saida" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Número do Ticket <span class="form-required">*</span></label>
                            <input type="text" class="form-control" name="numero_ticket" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Cidade <span class="form-required">*</span></label>
                                <select class="form-control" name="id_cidade" required>
                                    <option value="">Selecione a cidade</option>
                                    <?php while ($cidade = $query_cidades->fetch_assoc()): ?>
                                        <option value="<?= $cidade['id'] ?>"><?= htmlspecialchars($cidade['nome']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Estado <span class="form-required">*</span></label>
                                <select class="form-control" name="id_estado" required>
                                    <option value="">Selecione o estado</option>
                                    <?php while ($estado = $query_estados->fetch_assoc()): ?>
                                        <option value="<?= $estado['id'] ?>"><?= htmlspecialchars($estado['nome']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacao" rows="3"></textarea>
                        </div>

                        <div class="form-footer">
                            <button type="button" class="btn btn-primary" id="submitButton">
                                <i class="fas fa-check"></i> Registrar Saída
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal-box" style="max-width:520px;">
            <h2 class="modal-title">Confirmar Saída</h2>
            <p class="modal-desc">Revise os dados antes de confirmar o registro.</p>
            <table class="modal-table">
                <tr><td>Transferência</td><td id="modal_transferencia"></td></tr>
                <tr><td>Empresa de Origem</td><td id="modal_empresa_origem"></td></tr>
                <tr><td>Empresa de Destino</td><td id="modal_empresa_destino"></td></tr>
                <tr><td>Produto</td><td id="modal_produto"></td></tr>
                <tr><td>Setor</td><td id="modal_setor"></td></tr>
                <tr><td>Responsável</td><td id="modal_responsavel"></td></tr>
                <tr><td>Quantidade</td><td id="modal_quantidade"></td></tr>
                <tr><td>Tipo de Custo</td><td id="modal_tipo_custo"></td></tr>
                <tr><td>Data de Saída</td><td id="modal_data_saida"></td></tr>
                <tr><td>Nº do Ticket</td><td id="modal_ticket"></td></tr>
                <tr><td>Cidade</td><td id="modal_cidade"></td></tr>
                <tr><td>Estado</td><td id="modal_estado"></td></tr>
                <tr><td>Observações</td><td id="modal_observacao"></td></tr>
            </table>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                <button type="button" class="btn btn-ghost" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-primary" id="confirmSubmit">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>

    <script>
    function fecharModal() {
        document.getElementById('confirmModal').classList.remove('open');
    }

    function validarFormulario() {
        const campos = [
            'select[name="id_empresa_origem"]',
            'select[name="id_produto"]',
            'select[name="id_setor"]',
            'input[name="responsavel"]',
            'input[name="quantidade"]',
            'select[name="tipo_custo"]',
            'input[name="data_saida"]',
            'input[name="numero_ticket"]',
            'select[name="id_cidade"]',
            'select[name="id_estado"]'
        ];

        if (document.getElementById('transferencia_sim').checked) {
            campos.push('select[name="id_empresa_destino"]');
        }

        let vazio = false;
        campos.forEach(sel => {
            const el = document.querySelector(sel);
            if (!el || !el.value) {
                vazio = true;
                el && el.classList.add('has-error');
            } else {
                el.classList.remove('has-error');
            }
        });

        if (vazio) {
            alert('Por favor, preencha todos os campos obrigatórios.');
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function () {

        // Transferência condicional
        document.querySelectorAll('input[name="transferencia_estoque"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const container = document.getElementById('empresa_destino_container');
                const select    = document.querySelector('select[name="id_empresa_destino"]');
                const mostrar   = document.getElementById('transferencia_sim').checked;
                container.classList.toggle('hidden', !mostrar);
                select.required = mostrar;
            });
        });

        // Validação no submit
        document.getElementById('formSaida').addEventListener('submit', function (e) {
            if (!validarFormulario()) e.preventDefault();
        });

        // Abre modal de confirmação
        document.getElementById('submitButton').addEventListener('click', function () {
            if (!validarFormulario()) return;

            const getText = sel => {
                const el = document.querySelector(sel);
                return el ? el.options[el.selectedIndex].text : '';
            };
            const getVal = sel => document.querySelector(sel)?.value || '';

            document.getElementById('modal_transferencia').textContent =
                document.getElementById('transferencia_sim').checked ? 'Sim' : 'Não';
            document.getElementById('modal_empresa_origem').textContent  = getText('select[name="id_empresa_origem"]');
            document.getElementById('modal_empresa_destino').textContent =
                !document.getElementById('empresa_destino_container').classList.contains('hidden')
                    ? getText('select[name="id_empresa_destino"]') : 'N/A';
            document.getElementById('modal_produto').textContent    = getText('select[name="id_produto"]');
            document.getElementById('modal_setor').textContent      = getText('select[name="id_setor"]');
            document.getElementById('modal_responsavel').textContent = getVal('input[name="responsavel"]');
            document.getElementById('modal_quantidade').textContent  = getVal('input[name="quantidade"]');
            document.getElementById('modal_tipo_custo').textContent  = getText('select[name="tipo_custo"]');
            document.getElementById('modal_data_saida').textContent  = getVal('input[name="data_saida"]');
            document.getElementById('modal_ticket').textContent      = getVal('input[name="numero_ticket"]');
            document.getElementById('modal_cidade').textContent      = getText('select[name="id_cidade"]');
            document.getElementById('modal_estado').textContent      = getText('select[name="id_estado"]');
            document.getElementById('modal_observacao').textContent  = getVal('textarea[name="observacao"]') || 'Sem observações';

            document.getElementById('confirmModal').classList.add('open');
        });

        // Confirma e submete
        document.getElementById('confirmSubmit').addEventListener('click', function () {
            document.getElementById('formSaida').submit();
        });

        // Fecha ao clicar no overlay
        document.getElementById('confirmModal').addEventListener('click', function (e) {
            if (e.target === this) fecharModal();
        });

        // Lotes
        function carregarLotes() {
            const produtoId = document.querySelector('select[name="id_produto"]').value;
            const empresaId = document.querySelector('select[name="id_empresa_origem"]').value;

            if (!produtoId || !empresaId) {
                document.getElementById('lotes_container').innerHTML =
                    '<p style="color:var(--ink-4);font-size:13px;">Selecione o produto e a empresa de origem.</p>';
                return;
            }

            fetch('search_for_lots.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_produto=${encodeURIComponent(produtoId)}&id_empresa_origem=${encodeURIComponent(empresaId)}`
            })
            .then(r => r.text())
            .then(html => { document.getElementById('lotes_container').innerHTML = html; })
            .catch(() => {
                document.getElementById('lotes_container').innerHTML =
                    '<p style="color:var(--danger);font-size:13px;">Erro ao buscar lotes.</p>';
            });
        }

        document.querySelector('select[name="id_produto"]').addEventListener('change', carregarLotes);
        document.querySelector('select[name="id_empresa_origem"]').addEventListener('change', carregarLotes);

        // Recalcula quantidade pelos lotes
        document.getElementById('lotes_container').addEventListener('input', function (e) {
            if (e.target.matches('input[name^="lotes"]')) {
                let total = 0;
                document.querySelectorAll('input[name^="lotes"]').forEach(inp => {
                    total += parseInt(inp.value) || 0;
                });
                document.getElementById('quantidade').value = total;
            }
        });
    });
    </script>
</body>
</html>
