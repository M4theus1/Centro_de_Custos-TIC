<?php
session_start();
include(__DIR__ . '/../config/config.php');

if (!isset($_GET['token'])) {
    die("Token inválido.");
}

$token = $_GET['token'];

// Verifica se o token é válido
$stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expiracao > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    die("Token inválido ou expirado.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nova_senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Atualiza a senha e remove o token
    $stmt = $mysqli->prepare("UPDATE usuarios SET senha = ?, reset_token = NULL, reset_expiracao = NULL WHERE id = ?");
    $stmt->bind_param("si", $nova_senha, $usuario['id']);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Senha redefinida com sucesso!'); window.location.href='index.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Defina uma Nova Senha</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <input type="password" name="senha" class="form-control" required placeholder="Digite sua nova senha">
            </div>
            <button type="submit" class="btn btn-primary">Alterar Senha</button>
        </form>
    </div>
</body>
</html>
