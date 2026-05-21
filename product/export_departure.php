<?php
include(__DIR__ . '/../config/config.php');

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="relatorio_saidas.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Empresa', 'Produto', 'Valor Unitario', 'Setor', 'Responsavel',
    'Quantidade', 'Tipo de Custo', 'Data Entrada', 'Data Saida',
    'Numero Ticket', 'Cidade', 'Estado', 'Observacao'
], ';');

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim    = $_GET['data_fim']    ?? '';
$empresa     = $_GET['empresa']     ?? '';
$produto     = $_GET['produto']     ?? '';
$tipo_custo  = $_GET['tipo_custo']  ?? '';
$setor       = $_GET['setor']       ?? '';
$responsavel = $_GET['responsavel'] ?? '';

$sql = "SELECT emp.nome AS empresa, p.nome AS produto, ep.valor_unitario,
               setr.nome AS setor, s.responsavel, s.quantidade, s.tipo_custo,
               ep.data_entrada, s.data_saida, s.numero_ticket,
               c.nome AS cidade, e.nome AS estado, s.observacao
        FROM saida_produto s
        JOIN empresas emp         ON s.id_empresa = emp.id
        JOIN produtos p           ON s.id_produto = p.id
        LEFT JOIN entrada_produto ep ON s.id_lote = ep.id_entrada
        JOIN setores setr         ON s.id_setor   = setr.id
        JOIN cidades c            ON s.id_cidade  = c.id
        JOIN estados e            ON s.id_estado  = e.id
        WHERE 1=1";

$params = [];
$types  = '';

if (!empty($data_inicio) && !empty($data_fim)) {
    $sql      .= " AND s.data_saida BETWEEN ? AND ?";
    $params[]  = $data_inicio;
    $params[]  = $data_fim;
    $types    .= 'ss';
}
if (!empty($empresa)) {
    $sql      .= " AND emp.nome LIKE ?";
    $params[]  = '%' . $empresa . '%';
    $types    .= 's';
}
if (!empty($produto)) {
    $sql      .= " AND p.nome LIKE ?";
    $params[]  = '%' . $produto . '%';
    $types    .= 's';
}
if (!empty($tipo_custo)) {
    $sql      .= " AND s.tipo_custo = ?";
    $params[]  = $tipo_custo;
    $types    .= 's';
}
if (!empty($setor)) {
    $sql      .= " AND setr.nome LIKE ?";
    $params[]  = '%' . $setor . '%';
    $types    .= 's';
}
if (!empty($responsavel)) {
    $sql      .= " AND s.responsavel LIKE ?";
    $params[]  = '%' . $responsavel . '%';
    $types    .= 's';
}

$sql .= " ORDER BY s.data_saida DESC";

$stmt = $mysqli->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['empresa'],
        $row['produto'],
        number_format($row['valor_unitario'], 2, ',', '.'),
        $row['setor'],
        $row['responsavel'],
        $row['quantidade'],
        $row['tipo_custo'],
        !empty($row['data_entrada']) ? date('d/m/Y', strtotime($row['data_entrada'])) : '',
        !empty($row['data_saida'])   ? date('d/m/Y', strtotime($row['data_saida']))   : '',
        $row['numero_ticket'],
        $row['cidade'],
        $row['estado'],
        $row['observacao']
    ], ';');
}

fclose($output);
exit;
