<?php
include('../config/config.php');

$ESTOQUE_MINIMO = 3;

$total = $mysqli->query("
SELECT COUNT(*) total
FROM estoque
WHERE quantidade < $ESTOQUE_MINIMO
")->fetch_assoc()['total'];

echo json_encode([
"total"=>$total
]);