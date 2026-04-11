<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
 
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = array();
}
 
// Retirer un plat
if (isset($_POST['retirer_id'])) {
    $id = (int)$_POST['retirer_id'];
    unset($_SESSION['panier'][$id]);
    header('Location: panier.php');
    exit(0);
}

// Aller au paiement
if (isset($_POST['payer'])) {
    if ($_POST['type_livraison'] === 'plus_tard') {
        $_SESSION['type_livraison']  = 'plus_tard';
        $_SESSION['heure_livraison'] = $_POST['heure_livraison'];
    } else {
        $_SESSION['type_livraison']  = 'immediate';
        $_SESSION['heure_livraison'] = null;
    }
    header('Location: paiement.php');
    exit(0);
}
 
// Vider le panier
if (isset($_POST['vider'])) {
    $_SESSION['panier'] = array();
    header('Location: panier.php');
    exit(0);
}
 
$plats = lirePlats();
 
// Calcul du total
$total = 0;
foreach ($_SESSION['panier'] as $pid => $qte) {
    $plat = chercherPlatParId($plats, $pid);
    if ($plat) {
        $total = $total + ($plat['prix'] * $qte);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panier - La Confrerie</title>
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
    <h2 class="ptitre">Mon Panier</h2>
 
    <?php if (empty($_SESSION['panier'])) {?>
 
        <p class="panier-vide">Votre panier est vide.</p>
        <a href="presentation.php" class="boutton">Voir la carte</a>
 
    <?php } else{ ?>

    <?php foreach ($_SESSION['panier'] as $pid => $qte) {
        $plat = chercherPlatParId($plats, $pid);
        if ($plat) {
            $sousTotal = $plat['prix'] * $qte;
    ?>
        <div class="ligneprofil">
            <p><?php echo $plat['nom']; ?> x<?php echo $qte; ?></p>
            <span><?php echo $sousTotal; ?> €</span>
            <form method="post" action="panier.php">
                <input type="hidden" name="retirer_id" value="<?php echo $pid; ?>">
                <button type="submit" class="panier-suppr">✕</button>
            </form>
        </div>
    <?php } } ?>

        <form method="post" action="panier.php">
 
            
            <div class="ligneprofil">
                <p>Livraison :</p>
                <span>
                    <input type="radio" name="type_livraison" value="immediate" checked>
                    Immédiate
                    <input type="radio" name="type_livraison" value="plus_tard">
                    Plus tard
                </span>
            </div>
 

            <div class="ligneprofil">
                <p>Date et heure :</p>
                <input type="datetime-local" name="heure_livraison" class="champ">
            </div>
 
            <input type="hidden" name="payer" value="1">
            <button type="submit" class="boutton">
                Valider et payer <?php echo $total; ?> €
            </button>
 
        </form>
 

        <form method="post" action="panier.php">
            <input type="hidden" name="vider" value="1">
            <button type="submit" class="boutton">Vider le panier</button>
        </form>
 
        <a href="page_accueil_connecte.php" class="creer-compte">← Retour à l'accueil</a>
 
    <?php } ?>
 
</fieldset>
</div>
 
</body>
</html>
