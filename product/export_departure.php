<?php
include(__DIR__ . '/../config/config.php');

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="relatorio_saidas.csv"');

$output = fopen('php://output', 'w');

// Cabeçalho do CSV
fputcsv($output, ['Empresa', 'Produto', 'Valor Unitario', 'Setor', 'Responsavel', 'Quantidade', 'Data Saida', 'Numero Ticket', 'Cidade', 'Estado', 'Observacao'], ';');

// Consulta SQL para obter os dados
$sql = "SELECT emp.nome AS empresa, 
       p.nome AS produto, 
       ep.valor_unitario,  
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
JOIN (
    SELECT ep1.*
    FROM entrada_produto ep1
    INNER JOIN (
        SELECT id_produto, MAX(data_entrada) AS max_data
        FROM entrada_produto
        GROUP BY id_produto
    ) latest ON ep1.id_produto = latest.id_produto 
            AND ep1.data_entrada = latest.max_data
) ep ON s.id_produto = ep.id_produto
JOIN setores setr ON s.id_setor = setr.id
JOIN cidades c ON s.id_cidade = c.id
JOIN estados e ON s.id_estado = e.id;";

$result = $mysqli->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['empresa'],
        $row['produto'],
        number_format($row['valor_unitario'], 2, ',', '.'),
        $row['setor'],
        $row['responsavel'],
        $row['quantidade'],
        date('d/m/Y', strtotime($row['data_saida'])),
        $row['numero_ticket'],
        $row['cidade'],
        $row['estado'],
        $row['observacao']
    ], ';');
}

fclose($output);
exit;
?>
