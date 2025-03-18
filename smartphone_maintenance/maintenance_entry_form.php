<?php
session_start();
include(__DIR__ . '/../config/config.php');

$id = intval($_POST['id']);
$descricao_servico = $_POST['descricao_servico'];
$imei = $_POST['imei'];
$data_servico = $_POST['data_servico'];
$valor = str_replace(',', '.', $_POST['valor']);
$nota_fiscal = "";

// Diretório para salvar os arquivos
$uploadDir = __DIR__ . '/../uploads';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Verifica se foi enviado um arquivo
if (!empty($_FILES['nota_fiscal']['name'])) {
    $file = $_FILES['nota_fiscal'];
    $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nomeArquivo = 'NF_' . time() . '.' . $extensao;
    $caminhoArquivo = $uploadDir . $nomeArquivo;

    if (move_uploaded_file($file['tmp_name'], $caminhoArquivo)) {
        $nota_fiscal = '/uploads/notas_fiscais/' . $nomeArquivo;
    } else {
        $_SESSION['error'] = 'Erro ao salvar a Nota Fiscal.';
        header("Location: maintenance_entry.php");
        exit;
    }
}

// Se for edição, mantém o caminho antigo caso não seja enviado um novo arquivo
if ($id) {
    $query = $mysqli->query("SELECT nota_fiscal FROM manutencao_celular WHERE id = $id");
    $dadosAntigos = $query->fetch_assoc();
    if (!$nota_fiscal) {
        $nota_fiscal = $dadosAntigos['nota_fiscal'];
    }
}

// Insere ou atualiza no banco
if ($id) {
    $sql = "UPDATE manutencao_celular SET descricao_servico='$descricao_servico', imei='$imei', data_servico='$data_servico',
            valor='$valor', nota_fiscal='$nota_fiscal' WHERE id=$id";
} else {
    $sql = "INSERT INTO manutencao_celular (descricao_servico, imei, data_servico, valor, nota_fiscal) 
            VALUES ('$descricao_servico', '$imei', '$data_servico', '$valor', '$nota_fiscal')";
}

if ($mysqli->query($sql)) {
    $_SESSION['success'] = 'Manutenção salva com sucesso!';
} else {
    $_SESSION['error'] = 'Erro ao salvar manutenção.';
}

header("Location: maintenance_menu.php");
exit;
