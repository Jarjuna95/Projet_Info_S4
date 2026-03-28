<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
redirecterSiMauvaisRole('client', './Connexion.php');

$clientId   = $_SESSION[SESSION_ID];
$commandes  = lireCommandes();
$commandeId = isset($_GET['commande_id']) ? (int)$_GET['commande_id'] : 0; // Récupère l id de la commande passé en GET dans l URL
$cmd        = chercherCommandeParId($commandes, $commandeId)// Cherche la commande par son id;

if ($cmd === false || $cmd['client_id'] != $clientId || $cmd['statut'] !== 'livree') {// Vérifie que la commande existe, appartient au client, et est bien livrée
    header('Location: ./profil.php');// Redirige vers le profil si la commande n est pas notable
    exit(0);
}

$message = "";
if (isset($_POST['envoyer_avis'])) {
    $note_livraison = isset($_POST['note_livraison']) ? (int)$_POST['note_livraison'] : 0;
    $note_produit   = isset($_POST['note_produit'])   ? $_POST['note_produit']        : '';
    $commentaire    = isset($_POST['commentaire'])     ? $_POST['commentaire']         : '';

    if ($note_livraison >= 1 && $note_livraison <= 5) {
        for ($i = 0; $i < count($commandes); $i++) {// Parcourt le tableau commandes pour trouver la bonne et ajouter les notes
            if ($commandes[$i]['id'] == $commandeId) {
                $commandes[$i]['note_livraison']   = $note_livraison;
                $commandes[$i]['note_produit']     = $note_produit;
                $commandes[$i]['avis_commentaire'] = htmlspecialchars($commentaire);
                break;
            }
        }
        ecrireCommandes($commandes);// Réécrit tout le fichier commande.json avec les notes ajoutées
        $message = "✅ Merci pour votre avis !";
    } else {
        $message = "❌ La note doit être entre 1 et 5.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avis de votre commande</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="fond-image">
<div class="page-centree">
    <div id="conteneur">

        <?php if ($message !== ""): ?>
            <p style="color:green;"><?php echo $message; ?></p>
            <?php if (strpos($message, '✅') !== false): ?>
                <a href="profil.php" class="boutton">← Retour au profil</a>
            <?php endif; ?>
        <?php endif; ?>

        <form name="notation" method="post" action="avis.php?commande_id=<?php echo $commandeId; ?>">
            <fieldset>
                <legend>Votre avis — Commande #<?php echo $commandeId; ?></legend>

                <div class="div1">Note de la livraison (1 à 5)</div>
                <div class="div2">
                    <input type="number" name="note_livraison" min="1" max="5" value="5" class="champ" />
                </div><br />

                <div class="div1">Qualité des produits</div>
                <div class="div2">
                    <select name="note_produit" class="champ">
                        <option value="excellent">Excellent</option>
                        <option value="bon">Bon</option>
                        <option value="moyen">Moyen</option>
                        <option value="horrible">Horrible</option>
                    </select>
                </div><br />

                <div class="div1">Commentaire</div>
                <div class="div2">
                    <textarea name="commentaire" class="champ" rows="3" placeholder="Dites-nous en plus..."></textarea>
                </div><br />

                <div class="div1"></div>
                <div class="div2">
                    <input type="submit" name="envoyer_avis" value="Envoyer mon avis" class="boutton" />
                </div><br />
            </fieldset>
        </form>
        <a href="profil.php">← Retour au profil</a>
    </div>
</div>
</body>
</html>
