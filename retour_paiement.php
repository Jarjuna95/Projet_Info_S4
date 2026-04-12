<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
require_once('./fonctionphp/getapikey.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
 
// Récupérer les paramètres envoyés par CYBank 
if(isset($_GET['transaction'])){
    $transaction = $_GET['transaction'];
} 
else{
    $transaction = '';
}
 
if(isset($_GET['montant'])){
    $montant = $_GET['montant'];
} 
else{
    $montant = '';
}
 
if(isset($_GET['vendeur'])){
    $vendeur = $_GET['vendeur'];
}
else{
    $vendeur = '';
}
 
if(isset($_GET['statut'])){
    $statut = $_GET['statut'];
} 
else{
    $statut = '';
}
 
if(isset($_GET['control'])){
    $control = $_GET['control'];
} 
else{
    $control = '';
}
?>
