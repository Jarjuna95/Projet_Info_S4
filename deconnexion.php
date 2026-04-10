<?php
require_once('./fonctionphp/constantes.inc.php');

session_start();
session_destroy(); 

header('Location: ./page_accueil.php?deconnecte=ok');
exit(0);
?>
