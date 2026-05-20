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

// Quand le livreur clique sur un bouton, fetch() envoie une requête POST vers cette même page grâce à isset($_POST['action'])
// On traite le statut et on renvoie une réponse JSON au lieu d'afficher du HTML
if (isset($_POST['action']) && isset($_POST['commande_id'])) {
    header('Content-Type: application/json');

    $cid      = (int)$_POST['commande_id'];
    $action   = $_POST['action'];
    $cmdVerif = chercherCommandeParId($commandes, $cid);

    // Vérifie que la commande existe et appartient bien à ce livreur
    if ($cmdVerif === false || $cmdVerif['livreur_id'] != $livreurId) {
        http_response_code(403);
        echo json_encode(['message' => 'Accès refusé.']);
        exit(0);
    }

    // Met à jour le statut selon le bouton cliqué et renvoie un message de confirmation
    if ($action === 'livree') {
        mettreAJourStatutCommande($cid, 'livree');
        echo json_encode(['message' => "✅ Commande #$cid marquée comme livrée."]);
    } elseif ($action === 'abandonnee') {
        mettreAJourStatutCommande($cid, 'abandonnee');
        echo json_encode(['message' => "⚠️ Commande #$cid marquée comme abandonnée."]);
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Action invalide.']);
    }
    exit(0);
}

// Filtre les commandes de ce livreur et les répartit par statut
$mesCommandes = commandesDuLivreur($commandes, $livreurId);
$enCours      = [];
$terminees    = [];
foreach ($mesCommandes as $c) {
    if ($c['statut'] === 'en_livraison') {
        $enCours[] = $c;
    } elseif ($c['statut'] === 'livree' || $c['statut'] === 'abandonnee') {
        $terminees[] = $c;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Livreur - La Confrérie</title>
    <link id="css_mode" rel="stylesheet" href="style.css">
</head>
<body id="accueil2">

    <h1 class="ptitre">📦 Mes livraisons</h1>
    <p class="ptitre" style="font-size:20px;">Bonjour <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></p>

    <!-- Zone d'affichage du message renvoyé par fetch() après action du livreur -->
    <div id="message"></div>

    <h2 class="ptitre" style="font-size:30px;">🚴 En cours de livraison</h2>

    <?php if (empty($enCours)): ?>
        <p class="texte-centre">Aucune commande en cours à livrer.</p>
    <?php else: ?>
        <?php foreach ($enCours as $cmd): ?>
            <?php
                $client     = chercherUtilisateurParId($utilisateurs, $cmd['client_id']);
                $nomClient  = $client ? htmlspecialchars($client['nom'] . ' ' . $client['prenom']) : 'Inconnu';
                $telClient  = $client ? htmlspecialchars($client['telephone']) : '';
                $adresse    = htmlspecialchars($cmd['adresse_livraison']);
                $adresseEnc = urlencode($cmd['adresse_livraison']);
            ?>
            <!-- id unique par carte pour que JS puisse la supprimer après le clic -->
            <fieldset id="carte-<?php echo $cmd['id']; ?>" style="max-width:700px;">
                    <legend>Commande #<?php echo $cmd['id']; ?></legend>

                    <div class="ligne-info">
                        <div class="label">Client :</div>
                        <div class="donnee"><?php echo $nomClient; ?></div>
                    </div>
                    <div class="ligne-info">
                        <div class="label">Téléphone :</div>
                        <div class="donnee"><a href="tel:<?php echo $telClient; ?>"><?php echo $telClient; ?></a></div>
                    </div>
                    <div class="ligne-info">
                        <div class="label">Date commande :</div>
                        <div class="donnee"><?php echo htmlspecialchars($cmd['date_commande']); ?></div>
                    </div>

                    <div id="cadre-gris-adresse">
                        <p class="titre-cadre">Adresse de livraison</p>
                        <p><?php echo $adresse; ?></p>
                        <?php if (!empty($cmd['etage'])): ?>
                            <p><?php echo htmlspecialchars($cmd['etage']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($cmd['code_interphone'])): ?>
                            <p>Interphone : <strong><?php echo htmlspecialchars($cmd['code_interphone']); ?></strong></p>
                        <?php endif; ?>
                        <a href="http://maps.google.com/?q=<?php echo $adresseEnc; ?>" target="_blank" id="lien-maps">🗺️ Ouvrir dans Maps</a>
                    </div>

                    <?php if (!empty($cmd['commentaire'])): ?> 
                        <div id="cadre-gris-instructions">
                            <p class="titre-cadre">Instructions</p>
                            <p><?php echo htmlspecialchars($cmd['commentaire']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div id="cadre-gris-instructions">
                        <p class="titre-cadre">Articles commandés</p>
                        <?php foreach ($cmd['plats'] as $ligne): ?> 
                            <?php $plat = chercherPlatParId($plats, $ligne['plat_id']); ?> 
                            <?php if ($plat): ?>
                                <p>× <?php echo $ligne['quantite']; ?> — <?php echo htmlspecialchars($plat['nom']); ?></p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <p><strong>Total : <?php echo number_format($cmd['prix_total'], 2); ?> €</strong></p>
                    </div>

                    <!-- json_encode() formate la chaîne PHP en valeur JS valide -->
                    <!-- htmlspecialchars() convertit les " en &quot; pour ne pas casser l'attribut onclick="..." -->
                    <div class="boutons-livraison">
                        <button type="button" onclick="marquerCommande(<?php echo $cmd['id']; ?>, 'livree', <?php echo htmlspecialchars(json_encode($nomClient)); ?>, <?php echo htmlspecialchars(json_encode($adresse)); ?>, '<?php echo number_format($cmd['prix_total'], 2); ?>')" id="btn-livraison-terminer">✅ Livraison terminée</button>
                        <button type="button" onclick="marquerCommande(<?php echo $cmd['id']; ?>, 'abandonnee', <?php echo htmlspecialchars(json_encode($nomClient)); ?>, <?php echo htmlspecialchars(json_encode($adresse)); ?>, '<?php echo number_format($cmd['prix_total'], 2); ?>')" class="btn-abandon">❌ Adresse introuvable</button>
                    </div>

            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>

    <div id="section-terminees" <?php if (empty($terminees)) echo 'style="display:none;"'; ?>>
        <h2 class="ptitre" style="font-size:30px;">📋 Livraisons terminées</h2>
        <div class="cadre-tableau-admin">
            <table>
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Adresse</th>
                        <th>Total</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="tbody-terminees">
                    <?php foreach ($terminees as $cmd): ?>
                        <?php
                            $client = chercherUtilisateurParId($utilisateurs, $cmd['client_id']);
                            if ($client) {
                                $nomC = htmlspecialchars($client['nom'] . ' ' . $client['prenom']);
                            } else {
                                $nomC = 'Inconnu';
                            }
                        ?>
                        <tr>
                            <td>#<?php echo $cmd['id']; ?></td>
                            <td><?php echo $nomC; ?></td>
                            <td><?php echo htmlspecialchars($cmd['adresse_livraison']); ?></td>
                            <td><?php echo number_format($cmd['prix_total'], 2); ?> €</td>
                            <td>
                                <?php if ($cmd['statut'] === 'livree'): ?>
                                    <span class="statut-livree">✅ Livrée</span>
                                <?php else: ?>
                                    <span class="statut-abandonnee">⚠️ Abandonnée</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="lien-deconnexion">
        <a href="deconnexion.php" class="boutton">🚪 Se déconnecter</a>
    </div>

    <script>
        // Reçoit l'id, l'action ('livree' ou 'abandonnee'), le nom du client, l'adresse et le prix
        // Ces infos permettent d'ajouter la ligne dans le tableau
        async function marquerCommande(commandeId, action, nomClient, adresse, prix) {

            // FormData prépare les données à envoyer en POST
            // PHP les lira dans $_POST['commande_id'] et $_POST['action']
            const donnees = new FormData();
            donnees.append('commande_id', commandeId);
            donnees.append('action',      action);

            try {
                // Envoie une requête POST vers Livreur.php
                const reponse = await fetch('./Livreur.php', {
                    method : 'POST',
                    body   : donnees
                });

                // Lit la réponse JSON renvoyée par le serveur PHP
                const resultat = await reponse.json();

                // Affiche le message de confirmation dans la zone #message
                document.getElementById('message').textContent = resultat.message;

                // Supprime la carte de cette commande du bloc "En cours de livraison"
                document.getElementById('carte-' + commandeId).remove();

                // Affiche la section "Livraisons terminées" si elle était cachée (display:none au départ)
                document.getElementById('section-terminees').style.display = '';

                // Choisit le badge de statut selon l'action effectuée
                const statut = action === 'livree'
                    ? '<span class="statut-livree">✅ Livrée</span>'
                    : '<span class="statut-abandonnee">⚠️ Abandonnée</span>';

                // Ajoute une nouvelle ligne dans le tableau des livraisons terminées
                // innerHTML += ajoute le HTML de la ligne à la suite des lignes existantes
                document.getElementById('tbody-terminees').innerHTML +=
                    '<tr>' +
                    '<td>#' + commandeId + '</td>' +
                    '<td>' + nomClient   + '</td>' +
                    '<td>' + adresse     + '</td>' +
                    '<td>' + prix        + ' €</td>' +
                    '<td>' + statut      + '</td>' +
                    '</tr>';

            } catch (erreur) {
                // Affiché si le serveur est inaccessible ou la réponse est invalide
                document.getElementById('message').textContent = "Erreur : " + erreur.message;
            }
        }
    </script>
    <script src="script.js"></script>

</body>
</html>
