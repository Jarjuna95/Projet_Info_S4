<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
redirecterSiMauvaisRole('client', './Connexion.php');

$clientId  = $_SESSION[SESSION_ID];
$commandes = lireCommandes();
$plats     = lirePlats();

if (isset($_GET['commande_id'])) {
    $commandeId = (int)$_GET['commande_id'];
} else {
    header('Location: profil.php');
    exit(0);
}

$cmd = chercherCommandeParId($commandes, $commandeId);

if ($cmd === false || $cmd['client_id'] != $clientId || $cmd['statut'] !== 'a_preparer') {
    header('Location: profil.php');
    exit(0);
}


if (isset($_POST['commande_id'])) {
    $ancienTotal   = $cmd['prix_total'];
    $nouveauxPlats = [];
    $nouveauTotal  = 0;

    foreach ($plats as $plat) {
        $qte = (int)$_POST['qte_' . $plat['id']];
        if ($qte > 0) {
            $nouveauxPlats[] = ['plat_id' => $plat['id'], 'quantite' => $qte];
            $nouveauTotal    = $nouveauTotal + ($plat['prix'] * $qte);
        }
    }

    for ($i = 0; $i < count($commandes); $i++) {
        if ($commandes[$i]['id'] == $commandeId) {
            $commandes[$i]['plats']      = $nouveauxPlats;
            $commandes[$i]['prix_total'] = $nouveauTotal;
            break;
        }
    }

    ecrireCommandes($commandes);

    if ($nouveauTotal > $ancienTotal) {
        $diff = $nouveauTotal - $ancienTotal;
        echo "⚠️ Commande mise à jour ! Différence à payer : " . number_format($diff, 2) . " €";
    } else {
        echo "✅ Commande mise à jour !";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier commande #<?php echo $commandeId; ?></title>
    <link id="css_mode" rel="stylesheet" href="style.css">
</head>
<body id="accueil2">

<header id="front">
    <a href="profil.php" class="panierbouton">← Profil</a>
    <h1>La Confrerie</h1>
</header>

<div class="page-centree">
<fieldset>
    <legend>Modifier commande #<?php echo $commandeId; ?></legend>

    <?php foreach ($plats as $plat) {
        $qteActuelle = 0;
        foreach ($cmd['plats'] as $ligne) {
            if ($ligne['plat_id'] == $plat['id']) {
                $qteActuelle = $ligne['quantite'];
            }
        }
    ?>
    <div class="ligneprofil">
        <p><?php echo htmlspecialchars($plat['nom']); ?> (<?php echo $plat['prix']; ?> €)</p>
        <button type="button" onclick="changerQte(<?php echo $plat['id']; ?>, -1)">−</button>
        <span id="qte_<?php echo $plat['id']; ?>"><?php echo $qteActuelle; ?></span>
        <button type="button" onclick="changerQte(<?php echo $plat['id']; ?>, 1)">+</button>
    </div>
    <?php } ?>

    <div class="ligneprofil">
        <p><strong>Total :</strong></p>
        <span id="total-affiche"><?php echo $cmd['prix_total']; ?> €</span>
    </div>

    <div id="message-modification"></div>

    <button class="boutton" onclick="sauvegarder(<?php echo $commandeId; ?>)">
        Sauvegarder
    </button>

</fieldset>
</div>

<script>
// Prix de chaque plat
var prix = {
    <?php foreach ($plats as $plat) { ?>
        <?php echo $plat['id']; ?>: <?php echo $plat['prix']; ?>,
    <?php } ?>
};

// Quantités actuelles
var quantites = {
    <?php foreach ($plats as $plat) {
        $qte = 0;
        foreach ($cmd['plats'] as $ligne) {
            if ($ligne['plat_id'] == $plat['id']) $qte = $ligne['quantite'];
        }
    ?>
        <?php echo $plat['id']; ?>: <?php echo $qte; ?>,
    <?php } ?>
};

</script>

<script src="script.js"></script>
</body>
</html>
