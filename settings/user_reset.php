<?php
include(__DIR__ . '/../config/config.php');

$id = $_GET['id'];
$novaSenha = password_hash('1q2w3e4r5t', PASSWORD_DEFAULT);  // Senha padrão

$sql = "UPDATE usuarios SET senha = '$novaSenha' WHERE id = $id";
if ($mysqli->query($sql)) {
    echo "<script>alert('Senha resetada com sucesso!'); window.location.href = 'user_menu.php';</script>";
} else {
    echo "Erro ao resetar senha: " . $mysqli->error;
}

$mysqli->close();
?>
