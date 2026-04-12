<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
require_once('./fonctionphp/getapikey.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');

if(!isset($_SESSION['panier']) || empty($_SESSION['panier'])){
    header('Location: panier.php');
    exit(0);
}
 
$plats        = lirePlats();
$utilisateurs = lireUtilisateurs();
$commandes    = lireCommandes();
$clientId = $_SESSION[SESSION_ID];
$client   = chercherUtilisateurParId($utilisateurs, $clientId);
$total      = 0;
$listePlats = array();
 
foreach($_SESSION['panier'] as $pid => $qte){
    $plat = chercherPlatParId($plats, $pid);
    if($plat){
        $total = $total + ($plat['prix'] * $qte);
 
        $ligne             = array();
        $ligne['plat_id']  = $pid;
        $ligne['quantite'] = $qte;
        $listePlats[]      = $ligne;
    }
}
 

if(isset($_SESSION['type_livraison'])){
    $typeLivraison = $_SESSION['type_livraison'];
} 
else{
    $typeLivraison = 'immediate';
}
 
if(isset($_SESSION['heure_livraison'])){
    $heureLivraison = $_SESSION['heure_livraison'];
} 
else{
    $heureLivraison = null;
}
 

if($typeLivraison === 'immediate'){
    $statut = 'a_preparer';
}
else{
    $statut = 'en_attente';
}
 
// creation dun nouveau id
$maxId = 0;
foreach ($commandes as $cmd) {
    if ($cmd['id'] > $maxId) {
        $maxId = $cmd['id'];
    }
}
$nouvelId = $maxId + 1;
 
// CYBANK
$vendeur     = 'SUPMECA_H';
$montant     = number_format($total, 2, '.', '');
$transaction = 'LACONFRERIE' . $clientId . $nouvelId; 
$retour      = 'http://' . $_SERVER['HTTP_HOST'] . '/retour_paiement.php';
$api_key     = getAPIKey($vendeur);
$control     = md5($api_key . '#' . $transaction . '#' . $montant . '#' . $vendeur . '#' . $retour . '#');
 
// permettra de mettre a jour le json
$_SESSION['commande_en_attente'] = array();
$_SESSION['commande_en_attente']['id']                        = $nouvelId;
$_SESSION['commande_en_attente']['client_id']                 = $clientId;
$_SESSION['commande_en_attente']['livreur_id']                = null;
$_SESSION['commande_en_attente']['plats']                     = $listePlats;
$_SESSION['commande_en_attente']['adresse_livraison']         = $client['adresse'];
$_SESSION['commande_en_attente']['code_interphone']           = '';
$_SESSION['commande_en_attente']['etage']                     = '';
$_SESSION['commande_en_attente']['commentaire']               = '';
$_SESSION['commande_en_attente']['statut']                    = $statut;
$_SESSION['commande_en_attente']['date_commande']             = date('Y-m-d H:i:s');
$_SESSION['commande_en_attente']['heure_livraison_souhaitee'] = $heureLivraison;
$_SESSION['commande_en_attente']['prix_total']                = $total;
$_SESSION['commande_en_attente']['statut_paiement']           = 'en_attente';
$_SESSION['commande_en_attente']['note_livraison']            = '';
$_SESSION['commande_en_attente']['note_produit']              = '';
$_SESSION['commande_en_attente']['avis_commentaire']          = '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - La Confrerie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="accueil2">
 
<header id="front">
    <a href="panier.php" class="panierbouton">← Panier</a>
    <h1>La Confrerie</h1>
    <a href="profil.php" class="connecterbouton">👤 Mon Profil</a>
</header>
 
<div class="page-centree">
<fieldset>
    <legend>Récapitulatif de la commande</legend>
 
    <?php foreach($_SESSION['panier'] as $pid => $qte){
        $plat = chercherPlatParId($plats, $pid);
        if($plat){ ?>
        <div class="ligneprofil">
            <p><?php echo $plat['nom']; ?> x<?php echo $qte; ?></p>
            <span><?php echo $plat['prix'] * $qte; ?> €</span>
        </div>
    <?php }} ?>
 
    <div class="panier-total">
        <span>Total</span>
        <strong><?php echo $total; ?> €</strong>
    </div>
 
    <div class="ligneprofil">
        <p>Adresse :</p>
        <span><?php echo $client['adresse']; ?></span>
    </div>
 
    <div class="ligneprofil">
        <p>Livraison :</p>
        <?php if ($typeLivraison === 'immediate') { ?>
            <span>Immédiate</span>
        <?php } else { ?>
            <span>Le <?php echo $heureLivraison; ?></span>
        <?php } ?>
    </div>
 

    <form action="https://www.plateforme-smc.fr/cybank/" method="POST">
        <input type="hidden" name="transaction" value="<?php echo $transaction; ?>">
        <input type="hidden" name="montant" value="<?php echo $montant; ?>">
        <input type="hidden" name="vendeur" value="<?php echo $vendeur; ?>">
        <input type="hidden" name="retour" value="<?php echo $retour; ?>">
        <input type="hidden" name="control" value="<?php echo $control; ?>">
        <input type="submit" value="Payer <?php echo $total; ?> € par carte" class="boutton">
    </form>
 
    <a href="panier.php" class="creer-compte">← Retour au panier</a>
 
</fieldset>
</div>
 
</body>
</html>
