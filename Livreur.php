<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');

session_start();

redirecterSiNonConnecte('./Connexion.php');
redirecterSiMauvaisRole('livreur', './Connexion.php');

$livreurId    = $_SESSION[SESSION_ID];
$commandes    = lireCommandes();
$plats        = lirePlats();
$utilisateurs = lireUtilisateurs();

$message = "";
if (isset($_POST['action']) && isset($_POST['commande_id'])) {
    $cid    = (int)$_POST['commande_id'];
    $action = $_POST['action'];
    $cmdVerif = chercherCommandeParId($commandes, $cid);

    if ($cmdVerif !== false && $cmdVerif['livreur_id'] == $livreurId) {
        if ($action === 'livree') {
            mettreAJourStatutCommande($cid, 'livree');
            $message = "✅ Commande #$cid marquée comme livrée.";
        } elseif ($action === 'abandonnee') {
            mettreAJourStatutCommande($cid, 'abandonnee');
            $message = "⚠️ Commande #$cid marquée comme abandonnée (adresse introuvable).";
        }
        $commandes = lireCommandes();
    }
}

$mesCommandes = commandesDuLivreur($commandes, $livreurId);
$enCours   = [];
$terminees = [];
foreach ($mesCommandes as $c) {
    if ($c['statut'] === 'en_livraison') {
        $enCours[] = $c;
    } elseif ($c['statut'] === 'livree' || $c['statut'] === 'abandonnee') {
        $terminees[] = $c;
    }
}

echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Livreur - La Confrérie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="fond-image">
<div class="page-centree">

    <div class="conteneur-titre">
        <h1 class="ptitre">📦 Mes livraisons</h1>
        <p>Bonjour ' . htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) . '</p>
    </div>';

if ($message !== "") {
    echo '<div class="message-info">' . htmlspecialchars($message) . '</div>';
}

echo '<h2 class="sous-titre">En cours de livraison</h2>';

if (empty($enCours)) {
    echo '<p class="texte-centre">Aucune commande en cours à livrer.</p>';
} else {
    foreach ($enCours as $cmd) {
        $client     = chercherUtilisateurParId($utilisateurs, $cmd['client_id']);
        $nomClient  = $client ? htmlspecialchars($client['nom'] . ' ' . $client['prenom']) : 'Inconnu';
        $telClient  = $client ? htmlspecialchars($client['telephone']) : '';
        $adresse    = htmlspecialchars($cmd['adresse_livraison']);
        $adresseEnc = urlencode($cmd['adresse_livraison']);

        echo '
        <div class="card-livraison">
            <fieldset id="card-livraison">
                <legend>Commande #' . $cmd['id'] . '</legend>
                <div class="ligne-info">
                    <div class="label">Client :</div>
                    <div class="donnee">' . $nomClient . '</div>
                </div>
                <div class="ligne-info">
                    <div class="label">Téléphone :</div>
                    <div class="donnee"><a href="tel:' . $telClient . '">' . $telClient . '</a></div>
                </div>
                <div id="cadre-gris-adresse">
                    <p class="titre-cadre">Adresse de livraison</p>
                    <p>' . $adresse . '</p>';

        if (!empty($cmd['etage'])) {
            echo '<p>' . htmlspecialchars($cmd['etage']) . '</p>';
        }
        if (!empty($cmd['code_interphone'])) {
            echo '<p>Interphone : <strong>' . htmlspecialchars($cmd['code_interphone']) . '</strong></p>';
        }

        echo '<a href="http://maps.google.com/?q=' . $adresseEnc . '" target="_blank" id="lien-maps">🗺️ Maps</a>
                </div>';

        if (!empty($cmd['commentaire'])) {
            echo '<div id="cadre-gris-instructions">
                    <p class="titre-cadre">Instructions</p>
                    <p>' . htmlspecialchars($cmd['commentaire']) . '</p>
                  </div>';
        }

        echo '<div id="cadre-gris-instructions">
                <p class="titre-cadre">Articles commandés</p>';

        foreach ($cmd['plats'] as $ligne) {
            $plat = chercherPlatParId($plats, $ligne['plat_id']);
            if ($plat) {
                echo '<p>× ' . $ligne['quantite'] . ' — ' . htmlspecialchars($plat['nom']) . '</p>';
            }
        }

        echo '<p><strong>Total : ' . number_format($cmd['prix_total'], 2) . ' €</strong></p>
              </div>
                <form method="post" action="Livreur.php">
                    <input type="hidden" name="commande_id" value="' . $cmd['id'] . '">
                    <div class="boutons-livraison">
                        <button type="submit" name="action" value="livree" id="btn-livraison-terminer">✅ Livraison terminée</button>
                        <button type="submit" name="action" value="abandonnee" class="btn-abandon">❌ Adresse introuvable</button>
                    </div>
                </form>
            </fieldset>
        </div>';
    }
}

if (!empty($terminees)) {
    echo '<h2 class="sous-titre">Livraisons terminées</h2>';
    foreach ($terminees as $cmd) {
        $client = chercherUtilisateurParId($utilisateurs, $cmd['client_id']);
        $nomC = $client ? htmlspecialchars($client['nom'] . ' ' . $client['prenom']) : 'Inconnu';
        $etat = ($cmd['statut'] === 'livree') ? '✅ Livrée' : '⚠️ Abandonnée';
        echo '<div class="card-livraison terminee">
                <span>Commande #' . $cmd['id'] . ' — ' . $nomC . ' — <strong>' . $etat . '</strong></span>
              </div>';
    }
}

echo '<div class="lien-deconnexion">
        <a href="deconnexion.php">Se déconnecter</a>
    </div>
</div>
</body>
</html>';
?>
