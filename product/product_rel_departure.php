<?php 
include(__DIR__ . '/../config/config.php');

// Definir variáveis para o filtro de datas
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Configuração da paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Consulta SQL base
$sql = "SELECT s.id_saida, 
       emp.nome AS empresa, 
       p.nome AS produto, 
       setr.nome AS setor, 
       s.responsavel, 
       s.quantidade, 
       s.data_saida, 
       s.numero_ticket, 
       c.nome AS cidade, 
       e.nome AS estado, 
       s.observacao
FROM saida_produto s
JOIN empresas emp ON s.id_empresa = emp.id
JOIN produtos p ON s.id_produto = p.id
JOIN setores setr ON s.id_setor = setr.id
JOIN cidades c ON s.id_cidade = c.id
JOIN estados e ON s.id_estado = e.id
WHERE 1=1"; // Facilita adição de filtros dinâmicos

$params = [];
$types = "";

// Adicionar filtro de datas, se fornecido
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql .= " AND s.data_saida BETWEEN ? AND ?";
    $params[] = $data_inicio;
    $params[] = $data_fim;
    $types .= "ss";
}

// Ordenação e paginação
$sql .= " ORDER BY s.data_saida DESC, s.id_saida ASC LIMIT ? OFFSET ?";
$params[] = $registros_por_pagina;
$params[] = $offset;
$types .= "ii";


// Preparar a consulta SQL
$stmt = $mysqli->prepare($sql);
if ($stmt === false) {
    die("Erro na preparação da consulta: " . $mysqli->error);
}

// Vincular parâmetros
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Executar a consulta
$stmt->execute();
$result = $stmt->get_result();

// Contagem total de registros para paginação
$sql_total = "SELECT COUNT(*) AS total FROM saida_produto s WHERE 1=1";
$params_total = [];
$types_total = "";

if (!empty($data_inicio) && !empty($data_fim)) {
    $sql_total .= " AND s.data_saida BETWEEN ? AND ?";
    $params_total[] = $data_inicio;
    $params_total[] = $data_fim;
    $types_total .= "ss";
}

$stmt_total = $mysqli->prepare($sql_total);
if ($stmt_total === false) {
    die("Erro na preparação da consulta de contagem: " . $mysqli->error);
}

if (!empty($params_total)) {
    $stmt_total->bind_param($types_total, ...$params_total);
}

$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = max(1, ceil($total_registros / $registros_por_pagina));
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Saídas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Incluir o colResizable -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/colresizable/1.6.0/colResizable.min.css">

    <style>
        /* Estilo para a tabela ter tamanho fixo */
        .table-fixed {
            table-layout: fixed;
            width: 100%;
        }
        .table-fixed th, .table-fixed td {
            overflow: hidden; /* Oculta o conteúdo que ultrapassar */
            text-overflow: ellipsis; /* Adiciona "..." ao texto que ultrapassar */
            white-space: nowrap; /* Impede a quebra de linha */
        }
        .table-container {
            overflow-x: auto; /* Permite rolagem horizontal */
        }
        .table-spreadsheet {
            border: 1px solid #dee2e6;
        }
        .table-spreadsheet th, .table-spreadsheet td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }
        .table-spreadsheet th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .container {
            max-width: 95%;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .filter-form {
            margin-bottom: 20px;
        }
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
            width: calc(100% - 250px);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include(__DIR__ . '/../sidebar.php'); ?>

        <div class="main-content">
        <h1>Relatório de Saídas</h1>

        <!-- Formulário de Filtro por Datas -->
        <form method="GET" action="" class="filter-form">
            <div class="row">
                <div class="col-md-4">
                    <label for="data_inicio">Data Início:</label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($data_inicio); ?>">
                </div>
                <div class="col-md-4">
                    <label for="data_fim">Data Fim:</label>
                    <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($data_fim); ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Container da Tabela com Rolagem Horizontal -->
        <div class="table-container">
            <table class="table table-bordered table-spreadsheet table-fixed" id="resizableTable">
                <thead>
                    <tr>
                        <th style="width: 10%;">Empresa</th>
                        <th style="width: 10%;">Produto</th>
                        <th style="width: 10%;">Setor</th>
                        <th style="width: 12%;">Responsável</th>
                        <th style="width: 8%;">Quantidade</th>
                        <th style="width: 10%;">Data Saída</th>
                        <th style="width: 10%;">Número Ticket</th>
                        <th style="width: 10%;">Cidade</th>
                        <th style="width: 10%;">Estado</th>
                        <th style="width: 14%;">Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                            <td><?php echo htmlspecialchars($row['produto']); ?></td>
                            <td><?php echo htmlspecialchars($row['setor']); ?></td>
                            <td><?php echo htmlspecialchars($row['responsavel']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantidade']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['data_saida'])); ?></td>
                            <td><?php echo htmlspecialchars($row['numero_ticket']); ?></td>
                            <td><?php echo htmlspecialchars($row['cidade']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['observacao'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

        <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <!-- Incluir o colResizable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/colresizable/1.6.0/colResizable.min.js"></script>
    <script>
        // Aplicar colResizable à tabela
        $(document).ready(function() {
            $("#resizableTable").colResizable({
                liveDrag: true, // Permite redimensionamento em tempo real
                gripInnerHtml: "<div class='grip'></div>", // Estilo do "grip"
                minWidth: 50 // Largura mínima das colunas
            });
        });
    </script>
</body>
</html>