<?php
if(!isset($_SESSION)){
    session_start();
}

if(!isset($_SESSION['id'])){
   die("Login not authorized. <p><a href=\"/centro_de_custos/index.php\">Entrar</a></p");
}
?>