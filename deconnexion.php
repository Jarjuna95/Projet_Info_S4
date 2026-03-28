<?php
require_once('./constantes.inc.php');

session_start();

$_SESSION[SESSION_LOGIN] = "";
unset($_SESSION[SESSION_LOGIN]);
unset($_SESSION['role']);
unset($_SESSION['nom']);
unset($_SESSION['prenom']);

// On redirige vers la connexion avec un message
header('Location: ./Connexion.php?deconnecte=ok');
exit(0);
?>
