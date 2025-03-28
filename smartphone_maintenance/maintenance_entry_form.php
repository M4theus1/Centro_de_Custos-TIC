<?php
session_start();
include(__DIR__ . '/../config/config.php');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$descricao_servico = trim($_POST['descricao_servico']);
$imei = trim($_POST['imei']);
$data_servico = $_POST['data_servico'];
$valor = str_replace(',', '.', $_POST['valor']);
$nota_fiscal = "";

// Diretório de upload
$uploadDir = __DIR__ . '/../uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Upload de Nota Fiscal (se enviado)
if (!empty($_FILES['nota_fiscal']['name'])) {
    $file = $_FILES['nota_fiscal'];
    $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
    $tamanhoMaximo = 2 * 1024 * 1024; // 2MB

    if (!in_array($extensao, $permitidos)) {
        $_SESSION['error'] = "Formato de arquivo inválido! Apenas PDF, JPG e PNG são permitidos.";
        header("Location: maintenance_entry.php");
        exit;
    }

    if ($file['size'] > $tamanhoMaximo) {
        $_SESSION['error'] = "O arquivo deve ter no máximo 2MB.";
        header("Location: maintenance_entry.php");
        exit;
    }

    $nomeArquivo = uniqid('nf_', true) . '.' . $extensao;
    $caminhoArquivo = $uploadDir . '/' . $nomeArquivo;

    if (move_uploaded_file($file['tmp_name'], $caminhoArquivo)) {
        $nota_fiscal = '/uploads/' . $nomeArquivo;
    } else {
        $_SESSION['error'] = "Erro ao salvar a Nota Fiscal.";
        header("Location: maintenance_entry.php");
        exit;
    }
}

// Se for edição, mantém o caminho antigo se não houver novo arquivo
if ($id > 0) {
    $stmt = $mysqli->prepare("SELECT nota_fiscal FROM manutencao_celular WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dadosAntigos = $result->fetch_assoc();
    $stmt->close();

    if (!$nota_fiscal && isset($dadosAntigos['nota_fiscal'])) {
        $nota_fiscal = $dadosAntigos['nota_fiscal'];
    }
}

// Inserção ou atualização segura
if ($id > 0) {
    $stmt = $mysqli->prepare("UPDATE manutencao_celular SET descricao_servico=?, imei=?, data_servico=?, valor=?, nota_fiscal=? WHERE id=?");
    $stmt->bind_param("sssssi", $descricao_servico, $imei, $data_servico, $valor, $nota_fiscal, $id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO manutencao_celular (descricao_servico, imei, data_servico, valor, nota_fiscal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $descricao_servico, $imei, $data_servico, $valor, $nota_fiscal);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Manutenção salva com sucesso!";
} else {
    $_SESSION['error'] = "Erro ao salvar manutenção: " . $stmt->error;
}
$stmt->close();

header("Location: maintenance_menu.php");
exit;
?>
