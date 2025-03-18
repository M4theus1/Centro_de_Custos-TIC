<?php
session_start();
include(__DIR__ . '/../config/config.php');

$id = intval($_GET['id']);

if ($id && $mysqli->query("DELETE FROM manutencao_celular WHERE id = $id")) {
    $_SESSION['success'] = 'Manutenção excluída com sucesso!';
} else {
    $_SESSION['error'] = 'Erro ao excluir manutenção.';
}

header("Location: maintenance_menu.php");
exit;
