<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
 
$utilisateurs = lireUtilisateurs();
$id = $_SESSION[SESSION_ID];
 
for ($i = 0; $i < count($utilisateurs); $i++) {
    if ($utilisateurs[$i]['id'] == $id) {
        $utilisateurs[$i]['nom']    = $_POST['nom'];
        $utilisateurs[$i]['prenom'] = $_POST['prenom'];
        break;
    }
}
 
ecrireUtilisateurs($utilisateurs);
echo "✅ Profil mis à jour !";
?>
