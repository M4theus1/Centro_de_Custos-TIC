<?php
session_start();
include(__DIR__ . '/./config/config.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Verifica se as senhas coincidem
    if ($nova_senha == $confirmar_senha) {
        // Criptografa a nova senha
        $senhaCriptografada = password_hash($nova_senha, PASSWORD_DEFAULT);
        $id = $_SESSION['id'];

        // Atualiza a senha e remove a necessidade de troca
        $sql = "UPDATE usuarios SET senha = ?, precisa_trocar_senha = 0 WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('si', $senhaCriptografada, $id);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Senha alterada com sucesso!');
                    window.location.href='/centro_de_custos/dashboard/painel.php';
                  </script>";
        } else {
            echo "<script>alert('Erro ao atualizar senha.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('As senhas não coincidem.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Trocar Senha</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha</label>
                                <input type="password" name="nova_senha" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Nova Senha</label>
                                <input type="password" name="confirmar_senha" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Alterar Senha</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-link">Voltar ao Login</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS e dependências -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
