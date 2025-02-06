<?php 

if(!isset($_SESSION)){
    session_start();
 }

session_destroy();

header("Location: /centro_de_custos/index.php");
