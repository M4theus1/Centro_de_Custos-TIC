<?php
session_start();
include(__DIR__ . '/../config/config.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // PHPMailer carregado corretamente

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Verificar se o e-mail existe no banco
    $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    if ($usuario) {
        // Gerar token único e definir validade
        $token = bin2hex(random_bytes(50));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Salvar token no banco de dados
        $stmt = $mysqli->prepare("UPDATE usuarios SET reset_token = ?, reset_expiracao = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiracao, $email);
        $stmt->execute();
        $stmt->close();

        // Link para redefinição de senha
        $reset_link = "http://10.16.5.53/centro_de_custos/settings/user_reset_pass.php?token=$token";

        // Configuração do PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Servidor SMTP do Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'matheus.alves@dteltelecom.psi.br'; // E-mail de envio
            $mail->Password = 'ecrq ncae sbqg ksay'; // Senha de aplicativo do Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuração do e-mail
            $mail->setFrom('matheus.alves@dteltelecom.psi.br', 'Matheus da Silva Alves');
            $mail->addAddress($email); // Destinatário
            $mail->Subject = 'Redefinição de Senha';
            $mail->Body = "Clique no link para redefinir sua senha: $reset_link";

            // Enviar e-mail
            $mail->send();
            echo "<script>alert('E-mail de recuperação enviado! Verifique sua caixa de entrada.'); window.location.href='/centro_de_custos/index.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Erro ao enviar e-mail: {$mail->ErrorInfo}');</script>";
        }
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
