<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
redirecterSiMauvaisRole('client', './Connexion.php');

$clientId     = $_SESSION[SESSION_ID];
$utilisateurs = lireUtilisateurs();
$commandes    = lireCommandes();
$plats        = lirePlats();

$client = chercherUtilisateurParId($utilisateurs, $clientId);

$mesCommandes = [];
foreach ($commandes as $c) {
    if ($c['client_id'] == $clientId) {
        $mesCommandes[] = $c;
    }
}

// Prépare les données avant l affichage
$lignesCommandes = "";
if (empty($mesCommandes)) {
    $lignesCommandes = '<p>Aucune commande pour le moment.</p>';
} else {
    foreach ($mesCommandes as $cmd) {

        $nomPlats = [];
        foreach ($cmd['plats'] as $ligne) {
            $plat = chercherPlatParId($plats, $ligne['plat_id']);
            if ($plat) {
                $nomPlats[] = $ligne['quantite'] . '× ' . $plat['nom'];
            }
        }
        if ($cmd['statut'] === 'a_preparer')     $couleurStatut = 'gray';
        if ($cmd['statut'] === 'livree')         $couleurStatut = 'green';
        if ($cmd['statut'] === 'en_livraison')   $couleurStatut = 'orange';
        if ($cmd['statut'] === 'en_preparation') $couleurStatut = 'blue';
        if ($cmd['statut'] === 'abandonnee')     $couleurStatut = 'red';

        $boutonNote = "";
        if ($cmd['statut'] === 'livree' && empty($cmd['note_livraison'])) {
            $boutonNote = '<a href="avis.php?commande_id=' . $cmd['id'] . '" class="boutton"> Noter cette commande</a>';
        } elseif (!empty($cmd['note_livraison'])) {
            $boutonNote = '<p> Note livraison : ' . $cmd['note_livraison'] . '/5 — Produits : ' . $cmd['note_produit'] . '</p>';
        }

        $idCmd      = $cmd['id'];
        $listePlats = implode(', ', $nomPlats);
        $prix       = number_format($cmd['prix_total'], 2);
        $date       = $cmd['date_commande'];
        $statut     = strtoupper(str_replace('_', ' ', $cmd['statut']));

        $lignesCommandes .= "
        <div class='carte-produit'>
        <p><strong>Commande #$idCmd</strong></p>
        <p>$listePlats</p>
        <p><strong>$prix €</strong></p>
        <p>Le $date</p>
        <p style='color:$couleurStatut'><strong>$statut</strong></p>
        $boutonNote
        </div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profil - La Confrérie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="accueil2">
    <h1 class="ptitre">────── Profil utilisateur ──────</h1>
 
    <div class="profilbox">
        <section>
            <div class="premierelp">
                <h2 class="titre">Information de <?php echo $utilisateur['prenom'];</h2>
            </div>
            <div class="ligneprofil"><p>Nom :</p><span><?php echo $utilisateur['nom']; ?></span></div>
            <div class="ligneprofil"><p>Prénom :</p><span><?php echo $utilisateur['prenom']; ?></span></div>
            <div class="ligneprofil"><p>Email :</p><span><?php echo $utilisateur['login']; ?></span></div>
            <div class="ligneprofil"><p>Rôle :</p><span><?php echo ucfirst($utilisateur['role']); ?></span></div>
            <div class="ligneprofil"><p>Statut :</p><span><?php echo ucfirst($utilisateur['statut']); ?></span></div>
            <div class="ligneprofil"><p>Téléphone :</p><span><?php echo $utilisateur['telephone']; ?></span></div>
            <div class="ligneprofil"><p>Adresse :</p><span><?php echo $utilisateur['adresse']; ?></span></div>
            <div class="ligneprofil"><p>Inscription :</p><span><?php echo $utilisateur['date_inscription']; ?></span></div>
            <div class="ligneprofil"><p>Points fidélité :</p><span><?php echo $utilisateur['points_fidelite']; ?> pts</span></div>
        </section>
    </div>

    <div class="profilbox">
        <section>
            <h2>Historique des commandes</h2>
            <?php echo $lignesCommandes; ?>
        </section>
    </div>

    <div>
        <a href="admin.php" class="boutton">Retour à l'administration</a>
    </div>
</body>
</html>
