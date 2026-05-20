<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
// Vérifie que l'utilisateur est bien connecté, sinon le redirige vers la page de connexion
redirecterSiNonConnecte('./Connexion.php');
// Vérifie que l'utilisateur a le rôle 'client', sinon redirige
redirecterSiMauvaisRole('client', './Connexion.php');
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
// Quand le client clique sur "Envoyer", fetch() envoie une requête POST vers cette même page
// On détecte cela avec isset($_POST['note_livraison'])
if (isset($_POST['note_livraison'])) {

    // Toutes les réponses de ce bloc sont au format JSON
    // car c'est ce que fetch() attend pour faire reponse.json()
    header('Content-Type: application/json');

    // Récupération des données envoyées par fetch() via FormData
    // FormData envoie les données en POST, PHP les lit avec $_POST
    $noteLivraison = (int)$_POST['note_livraison'];
    $noteProduit   = isset($_POST['note_produit']) ? $_POST['note_produit'] : '';
    $commentaire   = isset($_POST['commentaire'])  ? $_POST['commentaire']  : '';

    // Validation de la note côté serveur : elle doit être entre 1 et 5
    if ($noteLivraison < 1 || $noteLivraison > 5) {
        // http_response_code(400) indique à fetch() que la requête est incorrecte
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
            <!-- Zone d'affichage des messages renvoyés par la réponse JSON de fetch() -->
            <div id="message"></div>
        
            <!-- La soumission est gérée par JavaScript via fetch()-->
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
                    <!-- Le clic déclenche la fonction JavaScript asynchrone envoyerAvis() -->
                    <button type="button" onclick="envoyerAvis()" class="boutton">Envoyer mon avis</button>
                </div><br />

            </form>
            <a href="profil.php">← Retour au profil</a>

            <script>
                // Fonction appelée au clic sur le bouton
                // Elle envoie les données du formulaire avec fetch()
                async function envoyerAvis() {

                    // FormData collecte les valeurs du formulaire et les prépare pour l'envoi en POST
                    // PHP les récupèrera ensuite dans $_POST
                    const donnees = new FormData();
                    donnees.append('note_livraison', parseInt(document.getElementById('note_livraison').value));
                    donnees.append('note_produit',   document.getElementById('note_produit').value);
                    donnees.append('commentaire',    document.getElementById('commentaire').value);

                    try {
                        // Envoie une requête POST asynchrone vers avis.php
                        // L'id de la commande est passé en paramètre GET dans l'URL
                        const reponse = await fetch('./avis.php?commande_id=<?php echo $commandeId; ?>', {
                            method : 'POST',
                            body   : donnees
                        });

                        // Lit la réponse JSON renvoyée par le serveur PHP
                        const resultat = await reponse.json();

                        // Affiche le message de retour dans la zone #message 
                        document.getElementById('message').textContent = resultat.message;

                        // Cache le formulaire pour ne plus pouvoir noter une deuxième fois
                        document.getElementById('formAvis').style.display = 'none';

                    } catch (erreur) {
                        // Affiché si le serveur est inaccessible ou la réponse est invalide
                        document.getElementById('message').textContent = "Erreur : " + erreur.message;
                    }
                }
            </script>
        <?php } ?>

    </fieldset>
</div>
<script src="script.js"></script>
</body>
</html>
