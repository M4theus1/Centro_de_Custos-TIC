<?php 
include(__DIR__ . '/../config/config.php');

$sql_code = "SELECT * FROM empresas WHERE ativo = 1";
$sql_query = $mysqli->query($sql_code) or die("Falha na execução do código SQL: " . $mysqli->error);

/**
 * Função para formatar o CNPJ.
 * Exemplo: 12345678000199 -> 12.345.678/0001-99
 */
function formatarCNPJ($cnpj) {
    return preg_replace(
        "/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/",
        "$1.$2.$3/$4-$5",
        $cnpj
    );
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/centro_de_custos/dashboard/painel.php">Centro de Custos TIC</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownConfig" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Configurações
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownConfig">
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
        <h2 class="mb-4">Lista de Empresas</h2>
        <a href="enterprise_create.php" class="btn btn-success mb-3">Adicionar Nova Empresa</a>
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $sql_query->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo formatarCNPJ($row['cnpj_empresa']); ?></td>
                        <td>
                            <a href="enterprise_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?');">Desativar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>

            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $mysqli->close(); ?>
