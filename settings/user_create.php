<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nivel_acesso = $_POST['nivel_acesso'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // Senha padrão criptografada
    $senhaPadrao = password_hash("1q2w3e4r5t", PASSWORD_DEFAULT);

    // Insere no banco com a senha padrão e define o campo precisa_trocar_senha como 1
    $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, ativo, precisa_trocar_senha) 
            VALUES (?, ?, ?, ?, ?, 1)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssssi', $nome, $email, $senhaPadrao, $nivel_acesso, $ativo);

    if ($stmt->execute()) {
        echo "<script>
                alert('Usuário cadastrado com sucesso! Senha padrão: 1q2w3e4r5t');
                window.location.href='user_menu.php';
              </script>";
    } else {
        echo "<script>alert('Erro ao cadastrar usuário.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Usuário</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Centro de Custos TIC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="user_menu.php">Usuários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard/painel.php">Dashboard</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Formulário -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Cadastrar Novo Usuário</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" name="nome" class="form-control" placeholder="Digite o nome completo" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" placeholder="Digite o e-mail" required>
                            </div>

                            <div class="mb-3">
                                <label for="nivel_acesso" class="form-label">Nível de Acesso</label>
                                <select name="nivel_acesso" class="form-select" required>
                                    <option value="ADMIN">Admin</option>
                                    <option value="USUARIO">Usuário</option>
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" name="ativo" class="form-check-input" id="ativo" checked>
                                <label class="form-check-label" for="ativo">Ativo</label>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-success">Cadastrar</button>
                                <a href="user_menu.php" class="btn btn-secondary">Voltar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
