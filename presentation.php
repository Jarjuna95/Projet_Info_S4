<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
 
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = array();
}
 
if (isset($_POST['plat_id'])) {
    $id = (int)$_POST['plat_id'];
    if (isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id] = $_SESSION['panier'][$id] + 1;
    } else {
        $_SESSION['panier'][$id] = 1;
    }
    header('Location: presentation.php');
    exit(0);
}
 
$plats = lirePlats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos Plats - La Confrerie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="accueil2">
 
<header id="front">
    <a href="page_accueil_connecte.php" class="panierbouton">Accueil</a>
    <h1>La Confrerie</h1>
    <a href="panier.php" class="connecterbouton">
        🛒 Panier
    </a>
</header>
 
<section>
    <h1 class="ptitre">────── Nos Produits ──────</h1>
 
    <div class="grille-produits">
        <?php foreach ($plats as $plat) { ?>
        <div class="carte-produit">
 
            <img src="<?php echo $plat['image']; ?>" alt="<?php echo $plat['nom']; ?>">
            <h3><?php echo $plat['nom']; ?></h3>
            <p><?php echo $plat['description']; ?></p>
            <span><?php echo $plat['prix']; ?> €</span>
 
            <form method="post" action="presentation.php">
                <input type="hidden" name="plat_id" value="<?php echo $plat['id']; ?>">
                <button type="submit" class="bouttonpanier">Ajouter au panier</button>
            </form>
 
            <?php if (isset($_SESSION['panier'][$plat['id']])) { ?>
                <p class="deja-panier">✓ <?php echo $_SESSION['panier'][$plat['id']]; ?> dans le panier</p>
            <?php } ?>
 
        </div>
        <?php } ?>
    </div>
</section>
 
</body>
</html>
