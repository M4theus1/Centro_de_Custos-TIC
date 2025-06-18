<?php
include(__DIR__ . '/../config/config.php');

$id_produto = $_POST['id_produto'] ?? null;
$id_empresa = $_POST['id_empresa_origem'] ?? null;

if (!$id_produto || !$id_empresa) {
    echo "<p class='text-danger'>Parâmetros inválidos.</p>";
    exit;
}

$query = $mysqli->prepare("
    SELECT ep.id_entrada, ep.quantidade, ep.valor_unitario, ep.data_entrada
    FROM entrada_produto ep
    WHERE ep.id_produto = ? AND ep.id_empresa = ? AND ep.quantidade > 0
    ORDER BY ep.data_entrada ASC
");
$query->bind_param("ii", $id_produto, $id_empresa);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-warning'>Nenhum lote disponível para esse produto nesta empresa.</p>";
    exit;
}

echo "<table class='table table-sm table-bordered'>
    <thead>
        <tr>
            <th>Lote (ID)</th>
            <th>Data Entrada</th>
            <th>Qtd Disponível</th>
            <th>Preço Unitário</th>
            <th>Qtd a Retirar</th>
        </tr>
    </thead>
    <tbody>";

while ($lote = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$lote['id_entrada']}</td>
        <td>" . date('d/m/Y', strtotime($lote['data_entrada'])) . "</td>
        <td>{$lote['quantidade']}</td>
        <td>R$ " . number_format($lote['valor_unitario'], 2, ',', '.') . "</td>
        <td>
            <input type='number' class='form-control form-control-sm' name='lotes[{$lote['id_entrada']}]' min='0' max='{$lote['quantidade']}' value='0'>
        </td>
    </tr>";
}

echo "</tbody></table>";
?>
