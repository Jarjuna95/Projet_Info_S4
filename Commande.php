<?php
require_once('./fonctionphp/constantes.inc.php'); 
require_once('./fonctionphp/fonctions.inc.php');  
session_start();                                  
redirecterSiNonConnecte('./Connexion.php');       
redirecterSiMauvaisRole('restaurateur', './Connexion.php'); 

$commandes    = lireCommandes();    
$utilisateurs = lireUtilisateurs(); 

$aPrerer     = "";
$enAttente   = "";
$enCours     = "";
$enLivraison = "";
$terminees   = "";

foreach ($commandes as $cmd) {
    $client = chercherUtilisateurParId($utilisateurs, $cmd['client_id']); // Cherche le client de la commande
    if ($client) {
        $nom    = htmlspecialchars($client['nom']);
        $prenom = htmlspecialchars($client['prenom']);
    } else {
        $nom    = 'Inconnu';
        $prenom = '';
    }
    $id = $cmd['id'];

    $ligne = "
    <tr>
        <td>$nom</td>
        <td>$prenom</td>
        <td>#$id</td>
        <td><a href='DetailCommande.php?commande_id=$id' class='boutton' style='padding:8px 16px;font-size:15px;'>🔍 Voir détail</a></td>
    </tr>";

    // Répartit chaque commande dans la bonne section selon son statut
    if ($cmd['statut'] === 'a_preparer')                                
        $aPrerer     .= $ligne;
    if ($cmd['statut'] === 'en_attente')                                
        $enAttente   .= $ligne;
    if ($cmd['statut'] === 'en_preparation')                            
        $enCours     .= $ligne;
    if ($cmd['statut'] === 'en_livraison')                              
        $enLivraison .= $ligne;
    if ($cmd['statut'] === 'livree' || $cmd['statut'] === 'abandonnee') 
        $terminees   .= $ligne;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes - La Confrérie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="accueil2">

    <h1 class="ptitre">Liste des commandes</h1>

    <h2 class="ptitre" style="font-size:30px;">À préparer</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>DÉTAIL</th></tr>
            <?php if ($aPrerer) { 
                echo $aPrerer; 
                } 
                else { 
                    echo '<tr><td colspan="4">Aucune</td></tr>'; 
                    } ?>
        </table>
    </div>

    <h2 class="ptitre" style="font-size:30px;">En attente</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>DÉTAIL</th></tr>
            <?php if ($enAttente) {
                 echo $enAttente; 
                 } else { 
                    echo '<tr><td colspan="4">Aucune</td></tr>'; 
                    } ?>
        </table>
    </div>

    <h2 class="ptitre" style="font-size:30px;">En préparation</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>DÉTAIL</th></tr>
            <?php if ($enCours) { 
                echo $enCours; 
                } 
                else { 
                    echo '<tr><td colspan="4">Aucune</td></tr>'; 
                    } ?>
        </table>
    </div>

    <h2 class="ptitre" style="font-size:30px;">En livraison</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>DÉTAIL</th></tr>
            <?php if ($enLivraison) {
                 echo $enLivraison; 
                 } else { 
                    echo '<tr><td colspan="4">Aucune</td></tr>'; 
                    } ?>
        </table>
    </div>

    <h2 class="ptitre" style="font-size:30px;">Terminées</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>DÉTAIL</th></tr>
            <?php if ($terminees) {
                 echo $terminees; 
                 } 
                else { 
                    echo '<tr><td colspan="4">Aucune</td></tr>'; 
                    } ?>
        </table>
    </div>

    <div class="lien-deconnexion">
        <a href="deconnexion.php" class="boutton">🚪 Se déconnecter</a>
    </div>

</body>
</html>
