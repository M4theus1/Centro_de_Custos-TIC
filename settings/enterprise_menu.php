<?php
include(__DIR__ . '/../config/config.php');

// Definir o limite de itens por página
define('LIMITE_EMPRESAS', 10);

// Obter o número da página atual
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;

// Calcular o deslocamento
$offset = ($pagina - 1) * LIMITE_EMPRESAS;

try {
    // Consulta para contar o total de empresas
    $sqlTotal = "SELECT COUNT(*) AS total FROM empresas WHERE ativo = 1";
    $stmtTotal = $mysqli->prepare($sqlTotal);
    if (!$stmtTotal->execute()) {
        throw new Exception("Erro ao contar empresas: " . $mysqli->error);
    }
    $resultTotal = $stmtTotal->get_result();
    $totalEmpresas = $resultTotal->fetch_assoc()['total'];

    // Consulta para buscar as empresas com paginação
    $sql = "SELECT * FROM empresas WHERE ativo = 1 LIMIT ? OFFSET ?";
    $stmt = $mysqli->prepare($sql);

    // Variáveis temporárias para o bind_param
    $limiteEmpresas = LIMITE_EMPRESAS;
    $stmt->bind_param('ii', $limiteEmpresas, $offset);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao buscar empresas: " . $mysqli->error);
    }
    $result = $stmt->get_result();

    // Calcular o total de páginas
    $totalPaginas = ceil($totalEmpresas / LIMITE_EMPRESAS);
} catch (Exception $e) {
    error_log("Erro: " . $e->getMessage()); // Registrar erro no log
    die("Ocorreu um erro. Por favor, tente novamente mais tarde.");
}

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #ffffff;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="main-content">
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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhuma empresa encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if ($pagina <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?php if ($pagina == $i) echo 'active'; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php if ($pagina >= $totalPaginas) echo 'disabled'; ?>">
                        <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>" aria-label="Próximo">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $mysqli->close(); ?>