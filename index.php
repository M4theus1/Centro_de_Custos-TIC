<?php
session_start();
include(__DIR__ . '/./config/config.php');

// Verifique se a conexão com o banco foi estabelecida
if (!$mysqli) {
    die("Erro ao conectar ao banco de dados: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $ip_acesso = $_SERVER['REMOTE_ADDR']; // Captura o IP do usuário
    $navegador_acesso = $_SERVER['HTTP_USER_AGENT']; // Captura o navegador do usuário
    $sucesso = 0; // Variável para indicar sucesso ou falha do login
    $usuario_id = null; // Inicializa o `usuario_id` como NULL para logins falhos

    // Prepara a consulta SQL para buscar o usuário
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Erro ao preparar consulta: " . $mysqli->error);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    // Verifica se o usuário existe e se a senha está correta
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Login bem-sucedido
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
        $sucesso = 1; // Login foi bem-sucedido
        $usuario_id = $usuario['id']; // Obtém o ID do usuário autenticado
    } else {
        // Login falhou
        echo "<script>alert('E-mail ou senha incorretos!');</script>";
    }

    // Insere o log de acesso no banco de dados
    $sql_log = "INSERT INTO logs_acesso (usuario_id, data_acesso, sucesso, ip_acesso, navegador_acesso) VALUES (?, NOW(), ?, ?, ?)";
    $stmt_log = $mysqli->prepare($sql_log);
    if (!$stmt_log) {
        die("Erro ao preparar consulta de log: " . $mysqli->error);
    }

    $stmt_log->bind_param('iiss', $usuario_id, $sucesso, $ip_acesso, $navegador_acesso);
    if (!$stmt_log->execute()) {
        die("Erro ao registrar log de acesso: " . $stmt_log->error);
    } else {
        echo "<script>console.log('Log de acesso registrado com sucesso.');</script>";
    }
    $stmt_log->close();

    // Redireciona após registrar o log
    if ($sucesso && $usuario['precisa_trocar_senha']) {
        header('Location: trocar_senha.php');
        exit();
    } elseif ($sucesso) {
        header('Location: /centro_de_custos/dashboard/painel.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Centro de Custos TIC</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h1 class="text-center mb-4">Acesse sua conta</h1>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="text" name="email" class="form-control" placeholder="Digite seu e-mail" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" required>
                    </div>

                    <p class="text-end mt-3">
                        <a href="/centro_de_custos/settings/user_forgot_pass.php">Esqueci minha senha</a>
                    </p>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>