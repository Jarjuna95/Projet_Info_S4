<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
 
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
 
<section>
    <h1 class="ptitre">────── Nos Produits ──────</h1>
 
    <div class="grille-produits">
        <?php foreach ($plats as $plat) { ?>
        <div class="carte-produit">
            <img src="<?php echo $plat['image']; ?>" alt="<?php echo $plat['nom']; ?>">
            <h3><?php echo $plat['nom']; ?></h3>
            <p><?php echo $plat['description']; ?></p>
            <span><?php echo $plat['prix']; ?> €</span>
        </div>
        <?php } ?>
    </div>
 
</section>
 
</body>
</html>
