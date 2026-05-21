<?php

header('Content-Type: application/json');

include('../config/config.php');

$sql="
SELECT p.nome, SUM(e.quantidade) as quantidade
FROM estoque e
JOIN produtos p ON p.id = e.id_produto
GROUP BY p.nome
ORDER BY quantidade DESC
LIMIT 10
";

$result = $mysqli->query($sql);

$labels = [];
$valores = [];

while($row = $result->fetch_assoc()){

$labels[] = $row['nome'];
$valores[] = (int)$row['quantidade'];

}

echo json_encode([
"labels"=>$labels,
"valores"=>$valores
]);