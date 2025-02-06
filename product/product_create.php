<?php
session_start();
include(__DIR__ . '/../config/config.php');

// Processar o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar e validar os dados do formulário
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $marca = filter_input(INPUT_POST, 'marca', FILTER_SANITIZE_STRING);

    if (!empty($nome)) {
        try {
            // Inserir produto no banco de dados
            $sql = "INSERT INTO produtos (nome, marca) VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ss', $nome, $marca);

            if ($stmt->execute()) {
                $_SESSION['mensagem'] = [
                    'tipo' => 'success',
                    'texto' => 'Produto cadastrado com sucesso!',
                ];
                header("Location: product_menu.php");
                exit();
            } else {
                throw new Exception("Erro ao cadastrar produto.");
            }
        } catch (Exception $e) {
            $_SESSION['mensagem'] = [
                'tipo' => 'danger',
                'texto' => 'Erro ao cadastrar produto. Por favor, tente novamente mais tarde.',
            ];
        } finally {
            $stmt->close();
        }
    } else {
        $_SESSION['mensagem'] = [
            'tipo' => 'danger',
            'texto' => 'Por favor, preencha o nome do produto.',
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="/centro_de_custos/dashboard/painel.php">Centro de Custos TIC</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNavDropdown">
    <ul class="navbar-nav">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Produtos
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="/centro_de_custos/product/product_stock.php">Estoque</a>       
          <a class="dropdown-item" href="/centro_de_custos/product/product_menu.php">Produto</a>
          <a class="dropdown-item" href="/centro_de_custos/product/product_entry.php">Entrada</a> 
          <a class="dropdown-item" href="/centro_de_custos/product/product_departure.php">Saída</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/centro_de_custos/settings/supplier_menu.php">Fornecedores</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/centro_de_custos/settings/user_menu.php">Usuários</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"></a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Configurações
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="/centro_de_custos/settings/enterprise_menu.php">Empresas</a>
          <a class="dropdown-item" href="/centro_de_custos/settings/state_menu.php">Estados</a>
          <a class="dropdown-item" href="/centro_de_custos/settings/city_menu.php">Cidades</a>
          <a class="dropdown-item" href="/centro_de_custos/settings/sector_menu.php">Setores</a>
        </div>
      </li>
    </ul>
  </div>
</nav>
    <h1>Cadastro de Produto</h1>

    <!-- Exibir mensagens de sucesso ou erro -->
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?php echo htmlspecialchars($_SESSION['mensagem']['tipo']); ?>" role="alert">
            <?php echo htmlspecialchars($_SESSION['mensagem']['texto']); ?>
        </div>
        <?php unset($_SESSION['mensagem']); ?>
    <?php endif; ?>

    <!-- Formulário de cadastro -->
    <form method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Produto <span class="text-danger">*</span></label>
            <input 
                type="text" 
                class="form-control" 
                id="nome" 
                name="nome" 
                aria-required="true" 
                required
                value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>">
        </div>
        <div class="mb-3">
            <label for="marca" class="form-label">Marca</label>
            <input 
                type="text" 
                class="form-control" 
                id="marca" 
                name="marca"
                value="<?php echo isset($marca) ? htmlspecialchars($marca) : ''; ?>">
        </div>
        <div class="d-flex justify-content-between mt-4 mb-3">
            <a href="/centro_de_custos/dashboard/painel.php" class="btn btn-secondary">Voltar ao Dashboard</a>
            <button type="submit" class="btn btn-primary">Cadastrar Produto</button>
        </div>
    </form>
</div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>
