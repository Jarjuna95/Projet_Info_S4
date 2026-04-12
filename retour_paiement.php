<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
require_once('./fonctionphp/getapikey.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
 
// Récupérer les paramètres envoyés par CYBank
if (isset($_GET['transaction'])) {
    $transaction = $_GET['transaction'];
} else {
    $transaction = '';
}
 
if (isset($_GET['montant'])) {
    $montant = $_GET['montant'];
} else {
    $montant = '';
}
 
if (isset($_GET['vendeur'])) {
    $vendeur = $_GET['vendeur'];
} else {
    $vendeur = '';
}
 
if (isset($_GET['statut'])) {
    $statut = $_GET['statut'];
} else {
    $statut = '';
}
 
if (isset($_GET['control'])) {
    $control = $_GET['control'];
} else {
    $control = '';
}
 
$api_key        = getAPIKey($vendeur);
$controlAttendu = md5($api_key . '#' . $transaction . '#' . $montant . '#' . $vendeur . '#' . $statut . '#');
 
// Comparer le control reçu avec celui qu on a calculé
if ($control === $controlAttendu) {
    $paiementValide = true;
} else {
    $paiementValide = false;
}
 
// Si paiement accepté et données valides on écrit dans le json
if ($paiementValide && $statut === 'accepted' && isset($_SESSION['commande_en_attente'])) {
 
    $commandes = lireCommandes();
 
    // Récupérer la commande stockée en session et marquer comme payée
    $nouvelleCommande = $_SESSION['commande_en_attente'];
    $nouvelleCommande['statut_paiement'] = 'paye';
 
    // Ajouter la commande au tableau et écrire dans le json
    $commandes[] = $nouvelleCommande;
    ecrireCommandes($commandes);
 
    // vider la session
    $_SESSION['panier']              = array();
    $_SESSION['type_livraison']      = null;
    $_SESSION['heure_livraison']     = null;
    $_SESSION['commande_en_attente'] = null;
 
    $message = 'Paiement accepté ! Votre commande a bien été enregistrée.';
 
} else {
 
    // Paiement refusé 
    $_SESSION['commande_en_attente'] = null;
    $message = 'Paiement refusé. Votre commande n\'a pas été enregistrée.';
 
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retour paiement - La Confrerie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="accueil2">
 
<header id="front">
    <a href="page_accueil_connecte.php" class="panierbouton">← Accueil</a>
    <h1>La Confrerie</h1>
    <a href="profil.php" class="connecterbouton">👤 Mon Profil</a>
</header>
 
<div class="page-centree">
<fieldset>
 
    <p><?php echo $message; ?></p>
 
    <?php if ($paiementValide && $statut === 'accepted') { ?>
        <a href="profil.php" class="boutton">Voir mes commandes</a>
    <?php } else { ?>
        <a href="panier.php" class="boutton">Retour au panier</a>
    <?php } ?>
 
</fieldset>
</div>
 
</body>
</html>
