<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
 
$categorie = $_GET['categorie'];
$plats = lirePlats();
 
if ($categorie !== 'tous') {
    $resultat = [];
    foreach ($plats as $plat) {
        if ($plat['categorie'] === $categorie) {
            $resultat[] = $plat;
        }
    }
    echo json_encode($resultat);
} else {
    echo json_encode($plats);
}
?>
