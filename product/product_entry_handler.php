<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Habilita a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtendo os dados do formulário
    $id_empresa = $_POST['id_empresa'];
    $id_produto = $_POST['id_produto'];
    $id_fornecedor = $_POST['id_fornecedor'];
    $quantidade = (int) $_POST['quantidade'];
    $valor_unitario = (float) $_POST['valor_unitario'];
    $frete = !empty($_POST['frete']) ? (float) $_POST['frete'] : 0; // Frete opcional
    $valor_total = (float) $_POST['valor_total'];
    $data_entrada = $_POST['data_entrada'];
    $nf = !empty($_POST['nf']) ? $_POST['nf'] : NULL; // NF opcional
    $observacao = $_POST['observacao'];

    // Validação básica (verificar se os campos obrigatórios estão preenchidos)
    if (empty($id_empresa) || empty($id_produto) || empty($id_fornecedor) || empty($quantidade) || empty($valor_unitario) || empty($data_entrada)) {
        $_SESSION['error'] = 'Todos os campos obrigatórios devem ser preenchidos!';
        header("Location: product_entry.php");
        exit();
    }

    // Inicia a transação para garantir a integridade dos dados
    $mysqli->begin_transaction();

    try {
        // Inserção dos dados na tabela 'entrada_produto'
        $query_entrada_produto = "INSERT INTO entrada_produto (id_empresa, id_produto, id_fornecedor, quantidade, valor_unitario, frete, valor_total, data_entrada, nf, observacao) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_entrada_produto = $mysqli->prepare($query_entrada_produto);
        if (!$stmt_entrada_produto) {
            throw new Exception('Erro na preparação da consulta para entrada_produto: ' . $mysqli->error);
        }
        $stmt_entrada_produto->bind_param('iiidddssss', $id_empresa, $id_produto, $id_fornecedor, $quantidade, $valor_unitario, $frete, $valor_total, $data_entrada, $nf, $observacao);
        $stmt_entrada_produto->execute();
        $stmt_entrada_produto->close();

        // Verifica se o produto já existe no estoque e atualiza ou insere
        $query_estoque = "SELECT quantidade FROM estoque WHERE id_empresa = ? AND id_produto = ?";
        $stmt_estoque = $mysqli->prepare($query_estoque);
        if (!$stmt_estoque) {
            throw new Exception('Erro na preparação da consulta de estoque: ' . $mysqli->error);
        }
        $stmt_estoque->bind_param('ii', $id_empresa, $id_produto);
        $stmt_estoque->execute();
        $stmt_estoque->store_result();

        if ($stmt_estoque->num_rows > 0) {
            // Produto já existe no estoque, atualiza a quantidade
            $stmt_estoque->bind_result($quantidade_estoque);
            $stmt_estoque->fetch();
            $nova_quantidade = $quantidade_estoque + $quantidade;

            // Atualiza os dados no estoque
            $query_update_estoque = "UPDATE estoque SET quantidade = ? WHERE id_empresa = ? AND id_produto = ?";
            $stmt_update_estoque = $mysqli->prepare($query_update_estoque);
            if (!$stmt_update_estoque) {
                throw new Exception('Erro na preparação da atualização do estoque: ' . $mysqli->error);
            }
            $stmt_update_estoque->bind_param('iii', $nova_quantidade, $id_empresa, $id_produto);
            $stmt_update_estoque->execute();
            $stmt_update_estoque->close();
        } else {
            // Produto não existe no estoque, insere uma nova entrada
            $query_insert_estoque = "INSERT INTO estoque (id_empresa, id_produto, quantidade) VALUES (?, ?, ?)";
            $stmt_insert_estoque = $mysqli->prepare($query_insert_estoque);
            if (!$stmt_insert_estoque) {
                throw new Exception('Erro na preparação da inserção do estoque: ' . $mysqli->error);
            }
            $stmt_insert_estoque->bind_param('iii', $id_empresa, $id_produto, $quantidade);
            $stmt_insert_estoque->execute();
            $stmt_insert_estoque->close();
        }
        $stmt_estoque->close();

        // Commit a transação
        $mysqli->commit();

        $_SESSION['success'] = 'Entrada de produto registrada com sucesso!';
        header("Location: product_entry.php");
        exit();
    } catch (Exception $e) {
        // Se algo der errado, desfaz a transação
        $mysqli->rollback();
        $_SESSION['error'] = 'Erro ao registrar a entrada de produto: ' . $e->getMessage();
        header("Location: product_entry.php");
        exit();
    }
}

// Fecha a conexão com o banco de dados
$mysqli->close();
?>