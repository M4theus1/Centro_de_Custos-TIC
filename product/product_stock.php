<?php
include(__DIR__ . '/../config/config.php');

$itens_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$inicio = ($pagina_atual - 1) * $itens_por_pagina;

$termo_pesquisa = isset($_GET['pesquisa']) ? $mysqli->real_escape_string($_GET['pesquisa']) : '';

$sql_base = "FROM estoque e
             JOIN empresas emp ON e.id_empresa = emp.id
             JOIN produtos p ON e.id_produto = p.id";

if (!empty($termo_pesquisa)) {
    $sql_base .= " WHERE emp.nome LIKE '%$termo_pesquisa%' OR p.nome LIKE '%$termo_pesquisa%'";
}

$sql_count = "SELECT COUNT(*) as total $sql_base";
$result_count = $mysqli->query($sql_count);
$total_registros = $result_count->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $itens_por_pagina);

$sql = "SELECT e.id_estoque, emp.nome AS empresa, p.nome AS produto, e.quantidade 
        $sql_base 
        LIMIT $inicio, $itens_por_pagina";

$result = $mysqli->query($sql);

$sql_alerta = "SELECT p.nome, e.quantidade FROM estoque e 
               JOIN produtos p ON e.id_produto = p.id 
               WHERE e.quantidade < 3";
$result_alerta = $mysqli->query($sql_alerta);

$produtos_alerta = [];
while ($row_alerta = $result_alerta->fetch_assoc()) {
    $produtos_alerta[] = $row_alerta;
}

if (!$result) {
    die("Erro na consulta: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .low-stock {
            background-color: #f8d7da !important; /* Vermelho claro */
        }
    </style>
</head>
<body>
    <?php include(__DIR__ . '/../sidebar.php'); ?>

    <div class="container mt-4">
        <h1>Estoque</h1>

        <div class="mb-4">
            <a href="/centro_de_custos/product/product_departure.php" class="btn btn-danger">
                REGISTRAR SAÍDA
            </a>
        </div>

        <!-- Barra de pesquisa -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar por empresa ou produto" value="<?php echo htmlspecialchars($termo_pesquisa); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>

        <!-- Tabela de Estoque -->
        <table class="table table-bordered table-spreadsheet">
            <thead>
                <tr>
                    <th>ID Estoque</th>
                    <th>Empresa</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo ($row['quantidade'] < 3) ? 'low-stock' : ''; ?>">
                        <td><?php echo htmlspecialchars($row['id_estoque']); ?></td>
                        <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                        <td><?php echo htmlspecialchars($row['produto']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantidade']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&pesquisa=<?php echo urlencode($termo_pesquisa); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- Modal de alerta -->
    <?php if (!empty($produtos_alerta)): ?>
        <div class="modal fade" id="alertaModal" tabindex="-1" aria-labelledby="alertaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="alertaModalLabel">⚠ Atenção! Baixo estoque</h5>
                    </div>
                    <div class="modal-body">
                        <p>Os seguintes produtos estão com menos de 3 unidades no estoque:</p>
                        <ul>
                            <?php foreach ($produtos_alerta as $produto): ?>
                                <li><strong><?php echo htmlspecialchars($produto['nome']); ?></strong> - Quantidade: <?php echo $produto['quantidade']; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Exibe o modal automaticamente se houver produtos com baixo estoque
        <?php if (!empty($produtos_alerta)): ?>
            var alertaModal = new bootstrap.Modal(document.getElementById('alertaModal'));
            alertaModal.show();
        <?php endif; ?>
    </script>
</body>
</html>
