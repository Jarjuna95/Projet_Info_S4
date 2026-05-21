<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
redirecterSiMauvaisRole('admin', './Connexion.php');

$clientId    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$utilisateurs = lireUtilisateurs();
$commandes    = lireCommandes();
$plats        = lirePlats();

if (isset($_POST['action_utilisateur']) && isset($_POST['user_id'])) {
    header('Content-Type: application/json');

    $uid    = (int)$_POST['user_id'];
    $action = $_POST['action_utilisateur'];

    // Parcourt les utilisateurs pour trouver le bon et modifier son statut
    $modifie = false;
    for ($i = 0; $i < count($utilisateurs); $i++) {
        if ($utilisateurs[$i]['id'] == $uid) {
            if ($action === 'bloquer') {
                $utilisateurs[$i]['statut'] = 'bloque';
                $modifie = true;
            } elseif ($action === 'debloquer') {
                $utilisateurs[$i]['statut'] = 'actif';
                $modifie = true;
            }
            break;
        }
    }

    if ($modifie) {
        // Sauvegarde le tableau mis à jour dans le fichier JSON
        ecrireUtilisateurs($utilisateurs);
        $nouveauStatut = ($action === 'bloquer') ? 'bloque' : 'actif';
        $msg           = ($action === 'bloquer') ? 'Utilisateur bloqué.' : 'Utilisateur débloqué.';
        // Renvoie le nouveau statut pour que JS mette à jour le bouton sans recharger
        echo json_encode(['message' => $msg, 'statut' => $nouveauStatut]);
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Erreur : utilisateur introuvable.']);
    }
    exit(0);
}

$utilisateur = chercherUtilisateurParId($utilisateurs, $clientId);

$mesCommandes = [];
foreach ($commandes as $c) {
    if ($c['client_id'] == $clientId) {
        $mesCommandes[] = $c;
    }
}

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
            $commentaire = !empty($cmd['avis_commentaire']) ? ' — ' . htmlspecialchars($cmd['avis_commentaire']) : '';
            $boutonNote  = '<p>Note livraison : ' . $cmd['note_livraison'] . '/5 — Produits : ' . $cmd['note_produit'] . $commentaire . '</p>';
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
    <h1 class="ptitre">────── Profil utilisateur ──────</h1>

    <div class="profilbox">
        <section>
            <div class="premierelp">
                <h2 class="titre">Information de <?php echo htmlspecialchars($utilisateur['prenom']); ?></h2>
            </div>
            <div class="ligneprofil"><p>Nom :</p><span><?php echo htmlspecialchars($utilisateur['nom']); ?></span></div>
            <div class="ligneprofil"><p>Prénom :</p><span><?php echo htmlspecialchars($utilisateur['prenom']); ?></span></div>
            <div class="ligneprofil"><p>Email :</p><span><?php echo htmlspecialchars($utilisateur['login']); ?></span></div>
            <div class="ligneprofil"><p>Rôle :</p><span><?php echo ucfirst($utilisateur['role']); ?></span></div>
            <div class="ligneprofil"><p>Statut :</p><span id="statut-affiche"><?php echo ucfirst($utilisateur['statut']); ?></span></div>
            <div class="ligneprofil"><p>Téléphone :</p><span><?php echo htmlspecialchars($utilisateur['telephone']); ?></span></div>
            <div class="ligneprofil"><p>Adresse :</p><span><?php echo htmlspecialchars($utilisateur['adresse']); ?></span></div>
            <div class="ligneprofil"><p>Inscription :</p><span><?php echo htmlspecialchars($utilisateur['date_inscription']); ?></span></div>
            <div class="ligneprofil"><p>Points fidélité :</p><span><?php echo $utilisateur['points_fidelite']; ?> pts</span></div>

            <div id="message"></div>

            <div class="admin_bouttons">
                <!-- Le bouton change selon le statut actuel de l'utilisateur -->
                <?php if ($utilisateur['statut'] === 'bloque'): ?>
                    <button type="button" onclick="gererUtilisateur(<?php echo $utilisateur['id']; ?>, 'debloquer')" class="boutton">Débloquer le compte</button>
                <?php else: ?>
                    <button type="button" onclick="gererUtilisateur(<?php echo $utilisateur['id']; ?>, 'bloquer')" class="boutton">Bloquer le compte</button>
                <?php endif; ?>
            </div>
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

    <script>
        // Fonction appelée au clic sur "Bloquer" ou "Débloquer"
        function gererUtilisateur(userId, action) {

            var xhr = new XMLHttpRequest();

            var donnees = 'user_id=' + userId + '&action_utilisateur=' + action;

            xhr.onreadystatechange = function() {
                // readyState == 4 : la réponse est complète 
                // status == 200 : le serveur a répondu sans erreur 
                if (this.readyState == 4 && this.status == 200) {

                    var resultat = JSON.parse(this.responseText);

                    // Affiche le message de confirmation (ex: "Utilisateur bloqué.")
                    document.getElementById('message').textContent = resultat.message;

                    document.getElementById('statut-affiche').textContent =
                        resultat.statut === 'bloque' ? 'Bloque' : 'Actif';

                    // Met à jour le bouton selon le nouveau statut
                    var btn = document.querySelector('.admin_bouttons button');
                    if (resultat.statut === 'bloque') {
                        btn.textContent = 'Débloquer le compte';
                        btn.onclick = function() { gererUtilisateur(userId, 'debloquer'); };
                    } else {
                        btn.textContent = 'Bloquer le compte';
                        btn.onclick = function() { gererUtilisateur(userId, 'bloquer'); };
                    }
                }
            };

            xhr.open('POST', './profil_version_admin.php?id=<?php echo $clientId; ?>', true);

            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.send(donnees);
        }
    </script>
    <script src="script.js"></script>
</body>
</html>
