<?php
session_start();
include(__DIR__ . '/../config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Verificar se o e-mail existe
    $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    if ($usuario) {
        // Gerar token único
        $token = bin2hex(random_bytes(50));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Salvar token no banco
        $stmt = $mysqli->prepare("UPDATE usuarios SET reset_token = ?, reset_expiracao = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiracao, $email);
        $stmt->execute();
        $stmt->close();

        // Enviar e-mail com o link de redefinição
        $reset_link = "http://seusite.com/resetar_senha.php?token=$token";
        $subject = "Redefinição de Senha";
        $message = "Clique no link para redefinir sua senha: $reset_link";
        $headers = "From: noreply@seusite.com";

        mail($email, $subject, $message, $headers);

        echo "<script>alert('E-mail de recuperação enviado! Verifique sua caixa de entrada.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('E-mail não encontrado.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Esqueci minha senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Recuperar Senha</h2>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required placeholder="Digite seu e-mail">
            </div>
            <button type="submit" class="btn btn-primary">Enviar Link de Redefinição</button>
        </form>
    </div>
</body>
</html>
