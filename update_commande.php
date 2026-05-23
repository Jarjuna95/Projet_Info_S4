<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
 
$commandeId   = (int)$_POST['commande_id'];
$nouveauStatut = $_POST['nouveau_statut'];
$livreurId     = $_POST['livreur_id'];
 
$commandes = lireCommandes();
 
for ($i = 0; $i < count($commandes); $i++) {
    if ($commandes[$i]['id'] == $commandeId) {
        $commandes[$i]['statut']     = $nouveauStatut;
        $commandes[$i]['livreur_id'] = $livreurId;
        break;
    }
}
 
ecrireCommandes($commandes);
echo "✅ Commande mise à jour !";
?>
