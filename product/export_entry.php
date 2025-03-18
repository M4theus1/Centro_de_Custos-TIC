<?php
include(__DIR__ . '/../config/config.php');

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="relatorio_entradas.csv"');

$output = fopen('php://output', 'w');

// Escrever cabeçalhos do CSV
fputcsv($output, ['Empresa', 'Produto', 'Fornecedor', 'Quantidade', 'Valor Unitário', 'Frete', 'Valor Total', 'Data Entrada', 'Nota Fiscal', 'Observação'], ';');

// Definir filtro de datas, se fornecido
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

$sql = "SELECT emp.nome AS empresa, p.nome AS produto, f.nome AS fornecedor, 
               e.quantidade, e.valor_unitario, e.frete, e.valor_total, e.data_entrada, e.nf, e.observacao 
        FROM entrada_produto e
        JOIN empresas emp ON e.id_empresa = emp.id
        JOIN produtos p ON e.id_produto = p.id
        JOIN fornecedores f ON e.id_fornecedor = f.id
        WHERE 1=1";

if (!empty($data_inicio) && !empty($data_fim)) {
    $sql .= " AND e.data_entrada BETWEEN '$data_inicio' AND '$data_fim'";
}

$sql .= " ORDER BY e.data_entrada DESC";

$result = $mysqli->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['empresa'],
        $row['produto'],
        $row['fornecedor'],
        $row['quantidade'],
        number_format($row['valor_unitario'], 2, ',', '.'),
        number_format($row['frete'], 2, ',', '.'),
        number_format($row['valor_total'], 2, ',', '.'),
        date('d/m/Y', strtotime($row['data_entrada'])),
        $row['nf'],
        $row['observacao']
    ], ';');
}

fclose($output);
exit;
?>
