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

$message     = "";
$typeMessage = ""; // "succes" ou "abandon"

// Traitement du formulaire POST (marquer livrée ou abandonnée)
if (isset($_POST['action']) && isset($_POST['commande_id'])) {
    $cid      = (int)$_POST['commande_id'];  
    $action   = $_POST['action'];            
    $cmdVerif = chercherCommandeParId($commandes, $cid); 

    if ($cmdVerif !== false && $cmdVerif['livreur_id'] == $livreurId) { // Vérifie que la commande existe et appartient bien au livreur
        if ($action === 'livree') {
            mettreAJourStatutCommande($cid, 'livree');
            $message     = "Commande #$cid marquée comme livrée.";
            $typeMessage = "succes";
        } elseif ($action === 'abandonnee') {
            mettreAJourStatutCommande($cid, 'abandonnee');
            $message     = "Commande #$cid marquée comme abandonnée (adresse introuvable).";
            $typeMessage = "abandon";
        }
        $commandes = lireCommandes(); 
    }
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
    <link rel="stylesheet" href="style.css">
</head>
<body id="accueil2">

    <h1 class="ptitre">📦 Mes livraisons</h1>
    <p class="texte-bienvenue">Bonjour <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></p>

    <?php if ($message !== ""): ?>
        <div class="message-livreur <?php echo $typeMessage; ?>">
            <?php if ($typeMessage === 'succes') { echo '✅ '; } else { echo '⚠️ '; } ?>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

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
            <div class="cadre-tableau-admin" style="max-width:700px;">
                <fieldset>
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

                    <form method="post" action="Livreur.php">
                        <input type="hidden" name="commande_id" value="<?php echo $cmd['id']; ?>"> 
                        <div class="boutons-livraison">
                            <button type="submit" name="action" value="livree" id="btn-livraison-terminer">✅ Livraison terminée</button>
                            <button type="submit" name="action" value="abandonnee" class="btn-abandon">❌ Adresse introuvable</button>
                        </div>
                    </form>

                </fieldset>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($terminees)): ?>
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
                <tbody>
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
    <?php endif; ?>

    <div style="text-align:center; margin: 40px auto 60px;">
        <a href="deconnexion.php" class="boutton" style="max-width:200px;">Se déconnecter</a>
    </div>

</body>
</html>
