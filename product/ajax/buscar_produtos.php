<?php
require(__DIR__ . '/../../config/config.php');
$search = $_GET['search'] ?? '';
$stmt = $mysqli->prepare("SELECT id, nome FROM produtos WHERE nome LIKE ? ORDER BY nome LIMIT 20");
$like = "%$search%";
$stmt->bind_param('s', $like);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
