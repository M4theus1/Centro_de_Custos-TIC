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
    $valor_unitario = (float) str_replace(',', '.', $_POST['valor_unitario']);
    $frete = !empty($_POST['frete']) ? (float) str_replace(',', '.', $_POST['frete']) : 0;
    $valor_total = (float) str_replace(',', '.', $_POST['valor_total']);
    $data_entrada = $_POST['data_entrada'];
    $observacao = $_POST['observacao'];

    // Definição do tamanho máximo permitido (2MB)
    $tamanho_maximo = 2 * 1024 * 1024; // 2MB em bytes

    // Variável para armazenar o caminho do arquivo
    $caminho_nf = NULL;

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

        // Cria o diretório de uploads se não existir
        $uploadDir = __DIR__ . '/../uploads'; // Ajuste o caminho conforme necessário
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Gera um nome único para o arquivo
        $nome_unico = uniqid('nf_', true) . '_' . basename($arquivo_nome); // Gera um nome único com prefixo 'nf_'
        $caminho_nf = $uploadDir . '/' . $nome_unico;

        // Move o arquivo para o diretório de uploads
        if (!move_uploaded_file($arquivo_tmp, $caminho_nf)) {
            $_SESSION['error'] = "Erro ao mover o arquivo para o diretório de uploads. Verifique as permissões.";
            header("Location: product_entry.php");
            exit();
        }
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
        $query_entrada_produto = "INSERT INTO entrada_produto (id_empresa, id_produto, id_fornecedor, quantidade, valor_unitario, frete, valor_total, data_entrada, nf, observacao) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_entrada_produto = $mysqli->prepare($query_entrada_produto);
        if (!$stmt_entrada_produto) {
            throw new Exception('Erro na preparação da consulta para entrada_produto: ' . $mysqli->error);
        }

        $stmt_entrada_produto->bind_param('iiidddssss', 
            $id_empresa, $id_produto, $id_fornecedor, $quantidade, $valor_unitario, $frete, $valor_total, 
            $data_entrada, $caminho_nf, $observacao
        );

        $stmt_entrada_produto->execute();
        $stmt_entrada_produto->close();

        // Verifica se o produto já existe no estoque
        $query_estoque = "SELECT id_estoque, quantidade FROM estoque WHERE id_empresa = ? AND id_produto = ?";
        $stmt_estoque = $mysqli->prepare($query_estoque);
        if (!$stmt_estoque) {
            throw new Exception('Erro na preparação da consulta para estoque: ' . $mysqli->error);
        }

        $stmt_estoque->bind_param('ii', $id_empresa, $id_produto);
        $stmt_estoque->execute();
        $stmt_estoque->store_result();

        if ($stmt_estoque->num_rows > 0) {
            // Produto já existe no estoque: atualiza a quantidade
            $stmt_estoque->bind_result($id_estoque, $quantidade_atual);
            $stmt_estoque->fetch();

            $nova_quantidade = $quantidade_atual + $quantidade;

            $query_update_estoque = "UPDATE estoque SET quantidade = ? WHERE id_estoque = ?";
            $stmt_update_estoque = $mysqli->prepare($query_update_estoque);
            if (!$stmt_update_estoque) {
                throw new Exception('Erro na preparação da consulta para atualizar estoque: ' . $mysqli->error);
            }

            $stmt_update_estoque->bind_param('ii', $nova_quantidade, $id_estoque);
            $stmt_update_estoque->execute();
            $stmt_update_estoque->close();
        } else {
            // Produto não existe no estoque: insere um novo registro
            $query_insert_estoque = "INSERT INTO estoque (id_empresa, id_produto, quantidade) VALUES (?, ?, ?)";
            $stmt_insert_estoque = $mysqli->prepare($query_insert_estoque);
            if (!$stmt_insert_estoque) {
                throw new Exception('Erro na preparação da consulta para inserir estoque: ' . $mysqli->error);
            }

            $stmt_insert_estoque->bind_param('iii', $id_empresa, $id_produto, $quantidade);
            $stmt_insert_estoque->execute();
            $stmt_insert_estoque->close();
        }

        $stmt_estoque->close();

        // Commit da transação
        $mysqli->commit();

        $_SESSION['success'] = 'Entrada de produto registrada com sucesso e estoque atualizado!';
        header("Location: product_entry.php");
        exit();
    } catch (Exception $e) {
        // Se algo der errado, desfaz a transação
        $mysqli->rollback();
        $_SESSION['error'] = 'Erro ao registrar a entrada de produto: ' . htmlspecialchars($e->getMessage());
        header("Location: product_entry.php");
        exit();
    }
}

// Fecha a conexão com o banco
$mysqli->close();
?>