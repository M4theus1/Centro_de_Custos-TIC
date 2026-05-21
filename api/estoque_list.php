<?php

header('Content-Type: application/json');

include('../config/config.php');

$ESTOQUE_MINIMO = 3;

$sql = "
SELECT 
e.id_estoque,
emp.nome empresa,
p.nome produto,
e.quantidade
FROM estoque e
JOIN empresas emp ON emp.id = e.id_empresa
JOIN produtos p ON p.id = e.id_produto
";

$result = $mysqli->query($sql);

$data = [];

while($row = $result->fetch_assoc()){

$status = $row['quantidade'] < $ESTOQUE_MINIMO
? "<span class='badge bg-danger'>Baixo</span>"
: "<span class='badge bg-success'>OK</span>";

$row['status'] = $status;

$data[] = $row;

}

echo json_encode([
"data"=>$data
]);