<?php
include(__DIR__ . '/../config/config.php');
session_start();

// Obter e validar o ID do produto
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'texto' => 'ID inválido ou produto não encontrado.',
    ];
    header("Location: product_menu.php");
    exit();
}

// Inicializar variáveis
$erro = null;

try {
    // Buscar produto pelo ID
    $sql = "SELECT * FROM produtos WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['mensagem'] = [
            'tipo' => 'danger',
            'texto' => 'Produto não encontrado.',
        ];
        header("Location: product_menu.php");
        exit();
    }

    $produto = $result->fetch_assoc();

    // Atualizar produto ao enviar o formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $marca = filter_input(INPUT_POST, 'marca', FILTER_SANITIZE_STRING);

        if ($nome && $marca) {
            $sql_update = "UPDATE produtos SET nome = ?, marca = ? WHERE id = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param('ssi', $nome, $marca, $id);

            if ($stmt_update->execute()) {
                $_SESSION['mensagem'] = [
                    'tipo' => 'success',
                    'texto' => 'Produto atualizado com sucesso!',
                ];
                header("Location: product_menu.php");
                exit();
            } else {
                $erro = "Erro ao atualizar produto. Por favor, tente novamente.";
            }
        } else {
            $erro = "Por favor, preencha todos os campos obrigatórios.";
        }
    }
} catch (Exception $e) {
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'texto' => 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.',
    ];
    header("Location: product_menu.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
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
    <div class="container mt-5">
        <h2 class="mb-4">Editar Produto</h2>
        
        <!-- Exibir mensagem de erro, se houver -->
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <!-- Formulário de edição -->
        <form method="POST">
            <div class="form-group">
                <label for="nome">Produto:</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="nome" 
                    name="nome" 
                    value="<?php echo htmlspecialchars($produto['nome']); ?>" 
                    required>
            </div>
            <div class="form-group">
                <label for="marca">Marca:</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="marca" 
                    name="marca" 
                    value="<?php echo htmlspecialchars($produto['marca']); ?>" 
                    required>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="product_menu.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>
</html>

<?php
$mysqli->close();
?>
