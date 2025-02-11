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
    $frete = !empty($_POST['frete']) ? (float) $_POST['frete'] : 0;
    $valor_total = (float) $_POST['valor_total'];
    $data_entrada = $_POST['data_entrada'];
    $observacao = $_POST['observacao'];

    // Definição do tamanho máximo permitido (2MB)
    $tamanho_maximo = 2 * 1024 * 1024; // 2MB em bytes

    // Variável para armazenar o conteúdo do arquivo
    $nf = NULL;
    $tipo_nf = NULL;

    // Processamento do arquivo da Nota Fiscal
    if (!empty($_FILES['nf']['name'])) {
        $arquivo_tmp = $_FILES['nf']['tmp_name'];
        $arquivo_nome = $_FILES['nf']['name'];
        $arquivo_tamanho = $_FILES['nf']['size']; // Obtém o tamanho do arquivo
        $extensao = strtolower(pathinfo($arquivo_nome, PATHINFO_EXTENSION));

        // Extensões permitidas
        $extensoes_permitidas = ['pdf', 'docx'];

        // Valida o tipo de arquivo
        if (!in_array($extensao, $extensoes_permitidas)) {
            $_SESSION['error'] = "Tipo de arquivo inválido! Apenas PDF e DOCX são permitidos.";
            header("Location: product_entry.php");
            exit();
        }

        // Valida o tamanho do arquivo
        if ($arquivo_tamanho > $tamanho_maximo) {
            $_SESSION['error'] = "O arquivo é muito grande! O tamanho máximo permitido é 2MB.";
            header("Location: product_entry.php");
            exit();
        }

        // Lê o conteúdo do arquivo
        $nf = file_get_contents($arquivo_tmp);
        $tipo_nf = $_FILES['nf']['type']; // Armazena o tipo MIME do arquivo
    }

    // Validação básica
    if (empty($id_empresa) || empty($id_produto) || empty($id_fornecedor) || empty($quantidade) || empty($valor_unitario) || empty($data_entrada)) {
        $_SESSION['error'] = 'Todos os campos obrigatórios devem ser preenchidos!';
        header("Location: product_entry.php");
        exit();
    }

    // Inicia transação
    $mysqli->begin_transaction();

    try {
        // Inserção dos dados na tabela 'entrada_produto'
        $query_entrada_produto = "INSERT INTO entrada_produto (id_empresa, id_produto, id_fornecedor, quantidade, valor_unitario, frete, valor_total, data_entrada, nf, tipo_nf, observacao) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_entrada_produto = $mysqli->prepare($query_entrada_produto);
        if (!$stmt_entrada_produto) {
            throw new Exception('Erro na preparação da consulta para entrada_produto: ' . $mysqli->error);
        }

        $stmt_entrada_produto->bind_param('iiidddssbss', 
            $id_empresa, $id_produto, $id_fornecedor, $quantidade, $valor_unitario, $frete, $valor_total, 
            $data_entrada, $nf, $tipo_nf, $observacao
        );

        // Vincula o arquivo binário
        $stmt_entrada_produto->send_long_data(8, $nf);
        $stmt_entrada_produto->execute();
        $stmt_entrada_produto->close();

        // Commit da transação
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

// Fecha a conexão com o banco
$mysqli->close();
?>
