<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtendo os dados do formulário
    $id_empresa_saida = $_POST['id_empresa']; // Empresa onde a saída será registrada
    $id_produto = $_POST['id_produto'];
    $id_setor = $_POST['id_setor'];
    $responsavel = $_POST['responsavel'];
    $quantidade = $_POST['quantidade'];
    $data_saida = $_POST['data_saida'];
    $numero_ticket = $_POST['numero_ticket'];
    $id_cidade = $_POST['id_cidade'];
    $id_estado = $_POST['id_estado'];
    $observacao = $_POST['observacao'];
    $id_empresa_origem = $_POST['id_empresa_origem']; // Nova variável para a empresa de origem

    // Validação básica (verificar se os campos obrigatórios estão preenchidos)
    if (empty($id_empresa_saida) || empty($id_produto) || empty($id_setor) || empty($responsavel) || empty($quantidade) || empty($data_saida) || empty($numero_ticket) || empty($id_cidade) || empty($id_estado) || empty($id_empresa_origem)) {
        $_SESSION['error'] = 'Todos os campos obrigatórios devem ser preenchidos!';
        header("Location: product_departure.php");
        exit();
    }

    // Inicia a transação para garantir a integridade dos dados
    $mysqli->begin_transaction();

    try {
        // Verifica se a empresa de saída é diferente da empresa de origem
        if ($id_empresa_saida != $id_empresa_origem) {
            // Realiza a transferência de estoque
            $query_estoque_origem = "SELECT quantidade FROM estoque WHERE id_empresa = ? AND id_produto = ?";
            $stmt_estoque_origem = $mysqli->prepare($query_estoque_origem);
            $stmt_estoque_origem->bind_param('ii', $id_empresa_origem, $id_produto);
            $stmt_estoque_origem->execute();
            $stmt_estoque_origem->store_result();

            if ($stmt_estoque_origem->num_rows > 0) {
                $stmt_estoque_origem->bind_result($quantidade_origem);
                $stmt_estoque_origem->fetch();

                if ($quantidade_origem >= $quantidade) {
                    // Reduz o estoque da empresa de origem
                    $nova_quantidade_origem = $quantidade_origem - $quantidade;
                    $query_update_origem = "UPDATE estoque SET quantidade = ? WHERE id_empresa = ? AND id_produto = ?";
                    $stmt_update_origem = $mysqli->prepare($query_update_origem);
                    $stmt_update_origem->bind_param('dii', $nova_quantidade_origem, $id_empresa_origem, $id_produto);
                    $stmt_update_origem->execute();
                    $stmt_update_origem->close();

                    // Aumenta o estoque da empresa de destino
                    $query_estoque_destino = "SELECT quantidade FROM estoque WHERE id_empresa = ? AND id_produto = ?";
                    $stmt_estoque_destino = $mysqli->prepare($query_estoque_destino);
                    $stmt_estoque_destino->bind_param('ii', $id_empresa_saida, $id_produto);
                    $stmt_estoque_destino->execute();
                    $stmt_estoque_destino->store_result();

                    if ($stmt_estoque_destino->num_rows > 0) {
                        $stmt_estoque_destino->bind_result($quantidade_destino);
                        $stmt_estoque_destino->fetch();
                        $nova_quantidade_destino = $quantidade_destino + $quantidade;
                    } else {
                        $nova_quantidade_destino = $quantidade;
                    }

                    $query_update_destino = "INSERT INTO estoque (id_empresa, id_produto, quantidade) VALUES (?, ?, ?) 
                                            ON DUPLICATE KEY UPDATE quantidade = ?";
                    $stmt_update_destino = $mysqli->prepare($query_update_destino);
                    $stmt_update_destino->bind_param('iiid', $id_empresa_saida, $id_produto, $nova_quantidade_destino, $nova_quantidade_destino);
                    $stmt_update_destino->execute();
                    $stmt_update_destino->close();

                    // Registra a transferência
                    $query_transferencia = "INSERT INTO transferencia_estoque (id_produto, id_empresa_origem, id_empresa_destino, quantidade, data_transferencia, observacao) 
                                           VALUES (?, ?, ?, ?, NOW(), ?)";
                    $stmt_transferencia = $mysqli->prepare($query_transferencia);
                    $stmt_transferencia->bind_param('iiiis', $id_produto, $id_empresa_origem, $id_empresa_saida, $quantidade, $observacao);
                    $stmt_transferencia->execute();
                    $stmt_transferencia->close();
                } else {
                    throw new Exception('Quantidade insuficiente no estoque da empresa de origem!');
                }
            } else {
                throw new Exception('Produto não encontrado no estoque da empresa de origem!');
            }
        }

        // Inserção dos dados na tabela 'saida_produto'
        $query_saida_produto = "INSERT INTO saida_produto (id_empresa, id_produto, id_setor, responsavel, quantidade, data_saida, numero_ticket, id_cidade, id_estado, observacao) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_saida_produto = $mysqli->prepare($query_saida_produto);
        if ($stmt_saida_produto) {
            $stmt_saida_produto->bind_param('iiisisssss', $id_empresa_saida, $id_produto, $id_setor, $responsavel, $quantidade, $data_saida, $numero_ticket, $id_cidade, $id_estado, $observacao);
            $stmt_saida_produto->execute();
            $stmt_saida_produto->close();
        } else {
            throw new Exception('Erro na preparação da consulta para saida_produto: ' . $mysqli->error);
        }

        // Commit da transação
        $mysqli->commit();

        $_SESSION['success'] = 'Saída de produto registrada com sucesso!';
        header("Location: product_departure.php");
    } catch (Exception $e) {
        // Se algo der errado, desfaz a transação
        $mysqli->rollback();
        $_SESSION['error'] = 'Erro ao registrar a saída de produto: ' . $e->getMessage();
        header("Location: product_departure.php");
    }
}

// Fecha a conexão com o banco de dados
$mysqli->close();
?>