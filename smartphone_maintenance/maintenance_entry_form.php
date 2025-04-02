<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Validação básica do ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Sanitização dos dados de entrada
$descricao_servico = trim(filter_input(INPUT_POST, 'descricao_servico', FILTER_SANITIZE_STRING));
$imei = trim(filter_input(INPUT_POST, 'imei', FILTER_SANITIZE_STRING));
$data_servico = filter_input(INPUT_POST, 'data_servico', FILTER_SANITIZE_STRING);
$valor = str_replace(',', '.', filter_input(INPUT_POST, 'valor', FILTER_SANITIZE_STRING));
$tipo_custo = trim(filter_input(INPUT_POST, 'tipo_custo', FILTER_SANITIZE_STRING));
$responsavel = trim(filter_input(INPUT_POST, 'responsavel', FILTER_SANITIZE_STRING));
$observacao = trim(filter_input(INPUT_POST, 'observacao', FILTER_SANITIZE_STRING));
$nota_fiscal = "";

// Validações básicas
if (empty($descricao_servico) || empty($imei) || empty($data_servico) || empty($valor) || empty($tipo_custo) || empty($responsavel)) {
    $_SESSION['error'] = "Todos os campos obrigatórios devem ser preenchidos!";
    header("Location: " . ($id > 0 ? "maintenance_edit.php?id=$id" : "maintenance_entry.php"));
    exit;
}

// Validação do valor numérico
if (!is_numeric($valor)) {
    $_SESSION['error'] = "O valor deve ser um número válido!";
    header("Location: " . ($id > 0 ? "maintenance_edit.php?id=$id" : "maintenance_entry.php"));
    exit;
}

// Validação da data
if (!DateTime::createFromFormat('Y-m-d', $data_servico)) {
    $_SESSION['error'] = "Data inválida!";
    header("Location: " . ($id > 0 ? "maintenance_edit.php?id=$id" : "maintenance_entry.php"));
    exit;
}

// Configurações de upload
$uploadDir = __DIR__ . '/../uploads';
$permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
$tamanhoMaximo = 2 * 1024 * 1024; // 2MB

// Verifica e cria o diretório de uploads
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        $_SESSION['error'] = "Falha ao criar diretório para uploads!";
        header("Location: " . ($id > 0 ? "maintenance_edit.php?id=$id" : "maintenance_entry.php"));
        exit;
    }
}

// Processamento do upload da nota fiscal
if (!empty($_FILES['nota_fiscal']['name'])) {
    $file = $_FILES['nota_fiscal'];
    $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validação do arquivo
    if (!in_array($extensao, $permitidos)) {
        $_SESSION['error'] = "Formato de arquivo inválido! Apenas PDF, JPG e PNG são permitidos.";
        header("Location: " . ($id > 0 ? "maintenance_edit.php?id=$id" : "maintenance_entry.php"));
        exit;
    }

    if ($file['size'] > $tamanhoMaximo) {
        $_SESSION['error'] = "O arquivo deve ter no máximo 2MB.";
        header("Location: " . ($id > 0 ? "maintenance_edit.php?id=$id" : "maintenance_entry.php"));
        exit;
    }

    // Gera nome único para o arquivo
    $nomeArquivo = uniqid('nf_', true) . '.' . $extensao;
    $caminhoArquivo = $uploadDir . '/' . $nomeArquivo;

    if (move_uploaded_file($file['tmp_name'], $caminhoArquivo)) {
        $nota_fiscal = '/uploads/' . $nomeArquivo;
    } else {
        $_SESSION['error'] = "Erro ao salvar a Nota Fiscal.";
        header("Location: " . ($id > 0 ? "maintenance_edit.php?id=$id" : "maintenance_entry.php"));
        exit;
    }
}

// Se for edição, mantém o arquivo antigo se não foi enviado um novo
if ($id > 0 && empty($nota_fiscal)) {
    $stmt = $mysqli->prepare("SELECT nota_fiscal FROM manutencao_celular WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dadosAntigos = $result->fetch_assoc();
    $stmt->close();

    if (!empty($dadosAntigos['nota_fiscal'])) {
        $nota_fiscal = $dadosAntigos['nota_fiscal'];
    }
}

// Prepara a query para inserção ou atualização
if ($id > 0) {
    $stmt = $mysqli->prepare("UPDATE manutencao_celular SET 
                             descricao_servico=?, 
                             imei=?, 
                             data_servico=?, 
                             valor=?, 
                             tipo_custo=?,
                             responsavel=?,
                             observacao=?,
                             nota_fiscal=? 
                             WHERE id=?");
    $stmt->bind_param("ssssssssi", 
                     $descricao_servico, 
                     $imei, 
                     $data_servico, 
                     $valor,
                     $tipo_custo,
                     $responsavel,
                     $observacao,
                     $nota_fiscal, 
                     $id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO manutencao_celular 
                            (descricao_servico, imei, data_servico, valor, tipo_custo, responsavel, nota_fiscal) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", 
                     $descricao_servico, 
                     $imei, 
                     $data_servico, 
                     $valor,
                     $tipo_custo,
                     $responsavel,
                     $nota_fiscal);
}

// Executa a query
if ($stmt->execute()) {
    $_SESSION['success'] = "Manutenção " . ($id > 0 ? "atualizada" : "registrada") . " com sucesso!";
} else {
    $_SESSION['error'] = "Erro ao salvar manutenção: " . $stmt->error;
}

$stmt->close();
$mysqli->close();

// Redireciona para a página apropriada
header("Location: maintenance_menu.php");
exit;
?>