<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
// Vérifie que l'utilisateur est bien connecté, sinon le redirige vers la page de connexion
redirecterSiNonConnecte('./Connexion.php');
// Vérifie que l'utilisateur a le rôle 'client', sinon redirige
redirecterSiMauvaisRole('client', './Connexion.php');
// Vérifie que le compte n'est pas bloqué : si oui, détruit la session sur-le-champ
redirecterSiBloquer('./Connexion.php');

// Récupère l'identifiant du client depuis la session
$clientId   = $_SESSION[SESSION_ID];
// Charge toutes les commandes depuis le fichier JSON
$commandes  = lireCommandes();
// Récupère l'id de la commande passé en paramètre GET dans l'URL
$commandeId = isset($_GET['commande_id']) ? (int)$_GET['commande_id'] : 0;
// Recherche la commande correspondante dans le tableau
$cmd        = chercherCommandeParId($commandes, $commandeId);

// Vérifie que la commande existe, appartient au client connecté, et est bien livrée
if ($cmd === false || $cmd['client_id'] != $clientId || $cmd['statut'] !== 'livree') {
    // Redirige vers le profil si la commande n'est pas notable
    header('Location: ./profil.php');
    exit(0);
}

// Quand le client clique sur "Envoyer", XHR envoie une requête POST vers cette même page
// On détecte cela avec isset($_POST['note_livraison'])
// On traite la note et on renvoie une réponse JSON 
if (isset($_POST['note_livraison'])) {

    header('Content-Type: application/json');

    $noteLivraison = (int)$_POST['note_livraison'];
    $noteProduit   = isset($_POST['note_produit']) ? $_POST['note_produit'] : '';
    $commentaire   = isset($_POST['commentaire'])  ? $_POST['commentaire']  : '';

    // Validation de la note côté serveur : elle doit être entre 1 et 5
    if ($noteLivraison < 1 || $noteLivraison > 5) {
        http_response_code(400);
        echo json_encode(['message' => 'La note doit être un entier entre 1 et 5.']);
        exit(0);
    }

    // Vérifie que la commande n'a pas déjà été notée (une seule note par commande)
    if (!empty($cmd['note_livraison'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Cette commande a déjà été notée.']);
        exit(0);
    }

    // Parcourt le tableau des commandes pour trouver la bonne et ajoute les notes du client
    for ($i = 0; $i < count($commandes); $i++) {
        if ($commandes[$i]['id'] == $commandeId) {
            $commandes[$i]['note_livraison']   = $noteLivraison;
            $commandes[$i]['note_produit']     = $noteProduit;
            // htmlspecialchars protège contre les injections XSS dans le commentaire libre
            $commandes[$i]['avis_commentaire'] = htmlspecialchars($commentaire);
            break;
        }
    }

    // Sauvegarde du tableau mis à jour dans le fichier JSON
    ecrireCommandes($commandes);

    // Réponse de succès renvoyée au JavaScript
    echo json_encode(['message' => 'Merci pour votre avis ! Il a bien été enregistré.']);
    exit(0);
}

// Vérifie si la commande a déjà été notée (une seule notation autorisée par commande)
$dejaNote = !empty($cmd['note_livraison']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avis de votre commande</title>
    <link id="css_mode" rel="stylesheet" href="style.css">
</head>
<body class="fond-image">
<div class="page-centree">
    <fieldset>
        <legend>Votre avis — Commande #<?php echo $commandeId; ?></legend>

        <?php if ($dejaNote) { ?>
            <!-- La commande a déjà une note : on affiche l'avis existant sans proposer le formulaire -->
            <p>✅ Vous avez déjà noté cette commande.</p>
            <p><strong>Note de livraison :</strong> <?php echo htmlspecialchars($cmd['note_livraison']); ?>/5</p>
            <p><strong>Qualité des produits :</strong> <?php echo htmlspecialchars($cmd['note_produit']); ?></p>
            <?php if (!empty($cmd['avis_commentaire'])) { ?>
                <p><strong>Commentaire :</strong> <?php echo htmlspecialchars($cmd['avis_commentaire']); ?></p>
            <?php } ?>
            <a href="profil.php" class="boutton">← Retour au profil</a>

        <?php } else { ?>
            <!-- Zone d'affichage des messages renvoyés par la réponse JSON de XHR -->
            <div id="message"></div>

            <form id="formAvis">

                <div class="div1">Note de la livraison (1 à 5)</div>
                <div class="div2">
                    <input type="number" id="note_livraison" name="note_livraison" min="1" max="5" value="5" class="champ" />
                </div><br />

                <div class="div1">Qualité des produits</div>
                <div class="div2">
                    <select id="note_produit" name="note_produit" class="champ">
                        <option value="excellent">Excellent</option>
                        <option value="bon">Bon</option>
                        <option value="moyen">Moyen</option>
                        <option value="horrible">Horrible</option>
                    </select>
                </div><br />

                <div class="div1">Commentaire</div>
                <div class="div2">
                    <textarea id="commentaire" name="commentaire" class="champ" rows="3" placeholder="Dites-nous en plus..."></textarea>
                </div><br />

                <div class="div1"></div>
                <div class="div2">
                    <button type="button" onclick="envoyerAvis()" class="boutton">Envoyer mon avis</button>
                </div><br />

            </form>
            <a href="profil.php">← Retour au profil</a>

            <script>

                function envoyerAvis() {

                    var xhr = new XMLHttpRequest();

                    // Lecture des valeurs du formulaire avec getElementById()
                    var noteLivraison = parseInt(document.getElementById('note_livraison').value);
                    var noteProduit   = document.getElementById('note_produit').value;
                    var commentaire   = document.getElementById('commentaire').value;

                    // PHP les récupèrera dans $_POST['note_livraison'], $_POST['note_produit'], $_POST['commentaire']
                    var donnees = 'note_livraison=' + noteLivraison + '&note_produit=' + noteProduit + '&commentaire=' + commentaire;

                    // Fonction appelée automatiquement à chaque changement d'état de la requête 
                    xhr.onreadystatechange = function() {
                        // readyState == 4 : la réponse est complète 
                        // status == 200 : le serveur a répondu sans erreur
                        if (this.readyState == 4 && this.status == 200) {

                            // Convertit la réponse JSON du serveur en objet JavaScript
                            var resultat = JSON.parse(this.responseText);

                            // Affiche le message de retour 
                            document.getElementById('message').textContent = resultat.message;

                            // Cache le formulaire pour ne plus pouvoir noter une deuxième fois
                            document.getElementById('formAvis').style.display = 'none';
                        }
                    };

                    xhr.open('POST', './avis.php?commande_id=<?php echo $commandeId; ?>', true);

                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    xhr.send(donnees);
                }
            </script>
        <?php } ?>

    </fieldset>
</div>
<script src="script.js"></script>
</body>
</html>
