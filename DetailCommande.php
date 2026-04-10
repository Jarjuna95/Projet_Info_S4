<?php
require_once('./fonctionphp/constantes.inc.php'); 
require_once('./fonctionphp/fonctions.inc.php'); 
session_start();                                 
redirecterSiNonConnecte('./Connexion.php');        
redirecterSiMauvaisRole('restaurateur', './Connexion.php'); 

$commandes    = lireCommandes();    
$plats        = lirePlats();        
$utilisateurs = lireUtilisateurs(); 
// Récupère tous les livreurs disponibles pour le select d'attribution
$livreurs = [];
foreach ($utilisateurs as $u) {
    if ($u['role'] === 'livreur') { // Ne garde que les utilisateurs ayant le rôle livreur
        $livreurs[] = $u;
    }
}

if (isset($_GET['commande_id'])) {           // Vérifie que l'id de la commande est bien passé en GET
    $commandeId = (int)$_GET['commande_id']; // Convertit en entier pour éviter les injections
} else {
    $commandeId = 0; 
}

$cmd = chercherCommandeParId($commandes, $commandeId); 

if ($cmd === false) {                
    header('Location: Commande.php'); // Redirige vers la liste des commandes
    exit(0);                         
}

// Récupère le client associé à la commande
$client = chercherUtilisateurParId($utilisateurs, $cmd['client_id']);

// Récupère le livreur associé à la commande, null si pas encore attribué
if ($cmd['livreur_id']) {
    $livreur = chercherUtilisateurParId($utilisateurs, $cmd['livreur_id']);
} else {
    $livreur = null;
}

// Textes lisibles pour chaque statut
$labelsStatut = [
    'a_preparer'     => 'À préparer',
    'en_attente'     => 'En attente',
    'en_preparation' => 'En préparation',
    'en_livraison'   => 'En livraison',
    'livree'         => 'Livrée',
    'abandonnee'     => 'Abandonnée',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail commande #<?php echo $commandeId; ?> - La Confrérie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="accueil2">

    <h1 class="ptitre" style="font-size:40px;">🧾 Détail commande #<?php echo $commandeId; ?></h1>

    <div class="cadre-tableau-admin" style="max-width:800px;">

        <h2 class="ptitre" style="font-size:22px;">Client</h2>
        <div class="ligne-info">
            <div class="label">Nom :</div>
            <div class="donnee">
                <?php if ($client) { 
                    echo htmlspecialchars($client['nom'] . ' ' . $client['prenom']); 
                    } 
                    else { 
                        echo 'Inconnu'; 
                        } ?>
            </div>
        </div>
        <div class="ligne-info">
            <div class="label">Téléphone :</div>
            <div class="donnee">
                <?php if ($client) {
                    echo htmlspecialchars($client['telephone']); 
                    } 
                    else { 
                        echo '—'; 
                        } ?>
            </div>
        </div>
        <div class="ligne-info">
            <div class="label">Date commande :</div>
            <div class="donnee"><?php echo htmlspecialchars($cmd['date_commande']); ?></div>
        </div>
        <div class="ligne-info">
            <div class="label">Statut actuel :</div>
            <div class="donnee">
                <strong>
                    <?php if (isset($labelsStatut[$cmd['statut']])) { 
                        echo $labelsStatut[$cmd['statut']]; 
                        } 
                        else { 
                            echo $cmd['statut']; 
                            } ?>
                </strong>
            </div>
        </div>
        <div class="ligne-info">
            <div class="label">Paiement :</div>
            <div class="donnee"><?php echo htmlspecialchars($cmd['statut_paiement']); ?></div>
        </div>

        <div id="cadre-gris-adresse">
            <p class="titre-cadre">Adresse de livraison</p>
            <p><?php echo htmlspecialchars($cmd['adresse_livraison']); ?></p>
            <?php if (!empty($cmd['etage'])) { ?>
                <p><?php echo htmlspecialchars($cmd['etage']); ?></p>
            <?php } ?>
            <?php if (!empty($cmd['code_interphone'])) { ?>
                <p>Interphone : <strong><?php echo htmlspecialchars($cmd['code_interphone']); ?></strong></p>
            <?php } ?>
        </div>

        <?php if (!empty($cmd['commentaire'])) { ?>
            <div id="cadre-gris-instructions">
                <p class="titre-cadre">Instructions client</p>
                <p><?php echo htmlspecialchars($cmd['commentaire']); ?></p>
            </div>
        <?php } ?>

        <div id="cadre-gris-instructions">
            <p class="titre-cadre">Articles commandés</p>
            <<?php foreach ($cmd['plats'] as $ligne) {
                $plat = chercherPlatParId($plats, $ligne['plat_id']);
                if ($plat) { ?>
                    <p>x<?php echo $ligne['quantite']; ?> — <?php echo htmlspecialchars($plat['nom']); ?></p>
                <?php }
                } ?>
            <p><strong>Total : <?php echo number_format($cmd['prix_total'], 2); ?> €</strong></p>
        </div>

        <div class="ligne-info">
            <div class="label">Livreur attribué :</div>
            <div class="donnee">
                <?php if ($livreur) {
                    echo htmlspecialchars($livreur['prenom'] . ' ' . $livreur['nom']);
                } else { ?>
                    <em>Aucun livreur attribué</em>
                <?php } ?>
            </div>
        </div>

        <!-- Formulaire changement de statut et attribution livreur  -->
        <form method="post" action="DetailCommande.php?commande_id=<?php echo $commandeId; ?>"> 
            <input type="hidden" name="commande_id" value="<?php echo $commandeId; ?>"> 

            <h2 class="ptitre" style="font-size:22px; margin-top:30px;">Modifier</h2>

            <div class="div1">Nouveau statut</div>
            <div class="div2">
                <select name="nouveau_statut" class="champ"> 
                    <?php foreach ($labelsStatut as $valeur => $texteStatut) { ?>
                        <option value="<?php echo $valeur; ?>" <?php if ($cmd['statut'] === $valeur) { echo 'selected'; } ?>>
                            <?php echo $texteStatut; ?>
                        </option>
                    <?php } ?>
                </select>
            </div><br />

            <div class="div1">Attribuer un livreur</div>
            <div class="div2">
                <select name="livreur_id" class="champ"> 
                    <option value="">Aucun</option> 
                    <?php foreach ($livreurs as $lv) { ?>
                        <option value="<?php echo $lv['id']; ?>" <?php if ($cmd['livreur_id'] == $lv['id']) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars($lv['prenom'] . ' ' . $lv['nom']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div><br />

            <div class="div2">
                <input type="submit" name="enregistrer" value="Enregistrer les modifications" class="boutton" /> <!-- Bouton de soumission pour phase 3) -->
            </div>

        </form>

    </div>

    <div class="lien-deconnexion">
        <a href="Commande.php" class="boutton" style="max-width:250px;">← Retour aux commandes</a>
    </div>

</body>
</html>

