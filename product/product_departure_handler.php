<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtendo os dados do formulário
    $id_empresa = $_POST['id_empresa'];
    $id_produto = $_POST['id_produto'];
    $id_setor = $_POST['id_setor'];
    $responsavel = $_POST['responsavel']; // Novo campo
    $quantidade = $_POST['quantidade'];
    $data_saida = $_POST['data_saida'];
    $numero_ticket = $_POST['numero_ticket'];
    $id_cidade = $_POST['id_cidade'];
    $id_estado = $_POST['id_estado']; // Novo campo
    $observacao = $_POST['observacao'];

    // Validação básica (verificar se os campos obrigatórios estão preenchidos)
    if (empty($id_empresa) || empty($id_produto) || empty($id_setor) || empty($responsavel) || empty($quantidade) || empty($data_saida) || empty($numero_ticket) || empty($id_cidade) || empty($id_estado)) {
        $_SESSION['error'] = 'Todos os campos obrigatórios devem ser preenchidos!';
        header("Location: product_departure.php");
        exit();
    }

    // Inicia a transação para garantir a integridade dos dados
    $mysqli->begin_transaction();

    try {
        // Inserção dos dados na tabela 'saida_produto'
        $query_saida_produto = "INSERT INTO saida_produto (id_empresa, id_produto, id_setor, responsavel, quantidade, data_saida, numero_ticket, id_cidade, id_estado, observacao) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_saida_produto = $mysqli->prepare($query_saida_produto);
        if ($stmt_saida_produto) {
            $stmt_saida_produto->bind_param('iiisisssss', $id_empresa, $id_produto, $id_setor, $responsavel, $quantidade, $data_saida, $numero_ticket, $id_cidade, $id_estado, $observacao);
            $stmt_saida_produto->execute();
            $stmt_saida_produto->close();
        } else {
            throw new Exception('Erro na preparação da consulta para saida_produto: ' . $mysqli->error);
        }

        // Verifica se o produto existe no estoque
        $query_estoque = "SELECT quantidade FROM estoque WHERE id_empresa = ? AND id_produto = ?";
        $stmt_estoque = $mysqli->prepare($query_estoque);
        if ($stmt_estoque) {
            $stmt_estoque->bind_param('ii', $id_empresa, $id_produto);
            $stmt_estoque->execute();
            $stmt_estoque->store_result();

            if ($stmt_estoque->num_rows > 0) {
                // Produto encontrado no estoque, verifica se a quantidade é suficiente
                $stmt_estoque->bind_result($quantidade_estoque);
                $stmt_estoque->fetch();

                if ($quantidade_estoque >= $quantidade) {
                    // Atualiza a quantidade no estoque
                    $nova_quantidade = $quantidade_estoque - $quantidade;
                    $query_update_estoque = "UPDATE estoque SET quantidade = ? WHERE id_empresa = ? AND id_produto = ?";
                    $stmt_update_estoque = $mysqli->prepare($query_update_estoque);
                    if ($stmt_update_estoque) {
                        $stmt_update_estoque->bind_param('dii', $nova_quantidade, $id_empresa, $id_produto);
                        $stmt_update_estoque->execute();
                        $stmt_update_estoque->close();
                    } else {
                        throw new Exception('Erro na atualização do estoque: ' . $mysqli->error);
                    }
                } else {
                    throw new Exception('Quantidade insuficiente no estoque!');
                }
            } else {
                throw new Exception('Produto não encontrado no estoque!');
            }
            $stmt_estoque->close();
        } else {
            throw new Exception('Erro na consulta de estoque: ' . $mysqli->error);
        }

        // Commit a transação
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