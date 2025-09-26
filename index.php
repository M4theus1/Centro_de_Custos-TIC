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
        if(isset($_POST['lembrar'])){
            setcookie('email_salvo', $email, time() + (86400 * 30), "/"); //30 dias
        } else{
            setcookie('email_salvo', '', time() - 3600, "/"); //Expira se desmarcado
        }
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
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <h2 class="text-center text-primary fw-bold mb-3">Centro de Custos TIC</h2>
                        <h2 class="text-center text-primary fw-bold mb-3">DTEL Telecom</h2>
                        <h4 class="text-center mb-4">Acesse sua conta</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Digite seu e-mail" required value="<?php echo isset($_COOKIE['email_salvo']) ? htmlspecialchars($_COOKIE['email_salvo']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" required>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="lembrar" id="lembrar" <?php echo isset($_COOKIE['email_salvo']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="lembrar">
                                    Lembrar de mim
                                </label>
                            </div>

                            <div class="text-end mb-3">
                                <a href="/centro_de_custos/settings/user_forgot_pass.php" class="small">Esqueci minha senha</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle + ícones -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>

</html>