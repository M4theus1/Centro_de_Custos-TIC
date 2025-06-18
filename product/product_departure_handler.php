<?php
session_start();
include(__DIR__ . '/../config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Dados do formulário
    $id_empresa_origem = $_POST['id_empresa_origem'];
    $id_empresa_destino = $_POST['id_empresa_destino'] ?? null;
    $id_produto = $_POST['id_produto'];
    $id_setor = $_POST['id_setor'];
    $responsavel = $_POST['responsavel'];
    $quantidade = $_POST['quantidade'];
    $data_saida = $_POST['data_saida'];
    $numero_ticket = $_POST['numero_ticket'];
    $id_cidade = $_POST['id_cidade'];
    $id_estado = $_POST['id_estado'];
    $observacao = $_POST['observacao'];
    $lotesSelecionados = $_POST['lotes'] ?? [];

    // Validação de campos obrigatórios
    if (
        empty($id_empresa_origem) || empty($id_produto) || empty($id_setor) || empty($responsavel) ||
        empty($quantidade) || empty($data_saida) || empty($numero_ticket) ||
        empty($id_cidade) || empty($id_estado)
    ) {
        $_SESSION['error'] = 'Todos os campos obrigatórios devem ser preenchidos!';
        header("Location: product_departure.php");
        exit();
    }

    if (empty($lotesSelecionados) || !is_array($lotesSelecionados)) {
        $_SESSION['error'] = 'Nenhum lote foi selecionado!';
        header("Location: product_departure.php");
        exit();
    }

    $mysqli->begin_transaction();

    try {
        // Valida empresa de origem
        $stmt = $mysqli->prepare("SELECT id FROM empresas WHERE id = ?");
        $stmt->bind_param('i', $id_empresa_origem);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) throw new Exception('Empresa de origem inválida!');
        $stmt->close();

        // Valida empresa de destino, se fornecida
        if ($id_empresa_destino) {
            $stmt = $mysqli->prepare("SELECT id FROM empresas WHERE id = ?");
            $stmt->bind_param('i', $id_empresa_destino);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) throw new Exception('Empresa de destino inválida!');
            $stmt->close();
        }

        $totalRetirar = 0;

        foreach ($lotesSelecionados as $id_lote => $qtd_retirada) {
            $qtd_retirada = (float) str_replace(',', '.', $qtd_retirada);
            if ($qtd_retirada <= 0) continue;

            // Consulta o lote
            $stmt = $mysqli->prepare("SELECT quantidade, valor_unitario FROM entrada_produto WHERE id_entrada = ? AND id_empresa = ? AND id_produto = ?");
            $stmt->bind_param('iii', $id_lote, $id_empresa_origem, $id_produto);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) throw new Exception("Lote ID $id_lote inválido!");
            $stmt->bind_result($qtd_disponivel, $preco_unitario);
            $stmt->fetch();
            $stmt->close();

            if ($qtd_disponivel < $qtd_retirada) {
                throw new Exception("Quantidade solicitada maior que disponível no lote ID $id_lote!");
            }

            // Atualiza o lote
            $nova_qtd_lote = $qtd_disponivel - $qtd_retirada;
            $stmt_update = $mysqli->prepare("UPDATE entrada_produto SET quantidade = ? WHERE id_entrada = ?");
            $stmt_update->bind_param('di', $nova_qtd_lote, $id_lote);
            $stmt_update->execute();
            $stmt_update->close();

            // Registra movimentação
            $stmt_mov = $mysqli->prepare("INSERT INTO estoque_movimento 
                (id_produto, tipo, id_origem, quantidade, valor_unitario, data_movimento) 
                VALUES (?, 'saida', ?, ?, ?, CURDATE())");
            $stmt_mov->bind_param('iiid', $id_produto, $id_empresa_origem, $qtd_retirada, $preco_unitario);
            $stmt_mov->execute();
            $stmt_mov->close();


            $totalRetirar += $qtd_retirada;
        }

        if ($totalRetirar <= 0) {
            throw new Exception("Quantidade total inválida (zero)!");
        }

        // Atualiza o estoque total
        $stmt = $mysqli->prepare("SELECT quantidade FROM estoque WHERE id_empresa = ? AND id_produto = ?");
        $stmt->bind_param('ii', $id_empresa_origem, $id_produto);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($quantidade_origem);
            $stmt->fetch();
            $nova_quantidade_origem = $quantidade_origem - $totalRetirar;
            $stmt_update = $mysqli->prepare("UPDATE estoque SET quantidade = ? WHERE id_empresa = ? AND id_produto = ?");
            $stmt_update->bind_param('dii', $nova_quantidade_origem, $id_empresa_origem, $id_produto);
            $stmt_update->execute();
            $stmt_update->close();
        }
        $stmt->close();

        // Registra a saída de produto
        $stmt_saida = $mysqli->prepare("INSERT INTO saida_produto 
            (id_empresa, id_produto, id_setor, responsavel, quantidade, data_saida, numero_ticket, id_cidade, id_estado, observacao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_saida->bind_param('iiisisssss', $id_empresa_origem, $id_produto, $id_setor, $responsavel, $totalRetirar, $data_saida, $numero_ticket, $id_cidade, $id_estado, $observacao);
        $stmt_saida->execute();
        $stmt_saida->close();

        // Registra transferência (se aplicável)
        if ($id_empresa_destino) {
            $stmt_transfer = $mysqli->prepare("INSERT INTO transferencia_estoque 
                (id_produto, id_empresa_origem, id_empresa_destino, quantidade, data_transferencia, observacao) 
                VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmt_transfer->bind_param('iiiis', $id_produto, $id_empresa_origem, $id_empresa_destino, $totalRetirar, $observacao);
            $stmt_transfer->execute();
            $stmt_transfer->close();
        }

        $mysqli->commit();
        $_SESSION['success'] = 'Saída de produto registrada com sucesso!';
    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = 'Erro ao registrar a saída de produto: ' . $e->getMessage();
    }

    header("Location: product_departure.php");
    exit();
}

$mysqli->close();
?>
