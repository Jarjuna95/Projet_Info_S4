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
    <link id="css_mode" rel="stylesheet" href="style.css">
</head>
<body id="accueil2">
    <h1 class="ptitre">────── Votre Profil ──────</h1>

    <div class="profilbox">
        <section>
            <div class="premierelp">
                <h2 class="titre">Informations personnelles</h2>
                <button id="crayon">✏️</button>
            </div>
            <div class="ligneprofil"><p>Nom :</p><span id="champ_nom"><?php echo htmlspecialchars($client['nom']); ?></span></div>
            <div class="ligneprofil"><p>Prénom :</p><span id="champ_prenom"><?php echo htmlspecialchars($client['prenom']); ?></span></div>
            <div class="ligneprofil"><p>Email :</p><span><?php echo htmlspecialchars($client['login']); ?></span></div>
            <div class="ligneprofil"><p>Points fidélité :</p><span><?php echo $client['points_fidelite']; ?> pts</span></div>
        </section>
    </div>

    <div class="profilbox">
        <section>
            <h2>Historique des commandes</h2>
            <?php echo $lignesCommandes; ?>
        </section>
    </div>

    <div class="lien-deconnexion">
        <a href="deconnexion.php" class="boutton">🚪 Se déconnecter</a>
    </div>
    <script>
        var boutonCrayon = document.getElementById('crayon');
        boutonCrayon.onclick = function() {

        if (boutonCrayon.textContent === '✏️') {
            var n = document.getElementById('champ_nom');
            var p = document.getElementById('champ_prenom');
            n.innerHTML = '<input type="text" id="input_nom" value="' + n.textContent + '">';
            p.innerHTML = '<input type="text" id="input_prenom" value="' + p.textContent + '">';
            boutonCrayon.textContent = '✅ Valider';

        } else {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('champ_nom').innerHTML    = document.getElementById('input_nom').value;
                    document.getElementById('champ_prenom').innerHTML = document.getElementById('input_prenom').value;
                    document.getElementById('message_profil').innerHTML = xhr.responseText;
                    boutonCrayon.textContent = '✏️';
                }
            };
            xhr.open("POST", "update_profil.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("nom=" + document.getElementById('input_nom').value + "&prenom=" + document.getElementById('input_prenom').value);
        }
        };
    </script>
    <script src="script.js"></script>
</body>
</html>
