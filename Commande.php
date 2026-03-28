<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
redirecterSiMauvaisRole('restaurateur', './Connexion.php');

$commandes    = lireCommandes();
$utilisateurs = lireUtilisateurs();

$aPrerer     = "";
$enCours     = "";
$enLivraison = "";
$terminees   = "";

foreach ($commandes as $cmd) {
    $client = chercherUtilisateurParId($utilisateurs, $cmd['client_id']);
    $nom    = $client ? htmlspecialchars($client['nom'])    : 'Inconnu';
    $prenom = $client ? htmlspecialchars($client['prenom']) : '';
    $id     = $cmd['id'];

    $ligne = "
    <tr>
        <td>$nom</td>
        <td>$prenom</td>
        <td>#$id</td>
        <td>
            <a href='DetailCommande.php?commande_id=$id' class='filtres button'>🔍 Voir détail</a>
            <button class='filtres button'>En cours de livraison</button>
            <button class='filtres button'>En cours de préparation</button>
        </td>
    </tr>";

    if ($cmd['statut'] === 'a_preparer')                                 $aPrerer     .= $ligne;
    if ($cmd['statut'] === 'en_preparation')                             $enCours     .= $ligne;
    if ($cmd['statut'] === 'en_livraison')                               $enLivraison .= $ligne;
    if ($cmd['statut'] === 'livree' || $cmd['statut'] === 'abandonnee')  $terminees   .= $ligne;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes - La Confrérie</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="fond-image">
<main id="accueil">

    <div class="conteneur-titre">
        <h1 class="ptitre">Liste des commandes</h1>
    </div>

    <h2 class="ptitre" style="font-size:30px;"> À préparer</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>ÉTAT</th></tr>
            <?php echo $aPrerer ?: '<tr><td colspan="4">Aucune</td></tr>'; ?>
        </table>
    </div>

    <h2 class="ptitre" style="font-size:30px;"> En préparation</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>ÉTAT</th></tr>
            <?php echo $enCours ?: '<tr><td colspan="4">Aucune</td></tr>'; ?>
        </table>
    </div>

    <h2 class="ptitre" style="font-size:30px;"> En livraison</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>ÉTAT</th></tr>
            <?php echo $enLivraison ?: '<tr><td colspan="4">Aucune</td></tr>'; ?>
        </table>
    </div>

    <h2 class="ptitre" style="font-size:30px;"> Terminées</h2>
    <div class="cadre-tableau-admin">
        <table>
            <tr><th>NOM</th><th>PRÉNOM</th><th>COMMANDE</th><th>ÉTAT</th></tr>
            <?php echo $terminees ?: '<tr><td colspan="4">Aucune</td></tr>'; ?>
        </table>
    </div>

    <div class="lien-deconnexion">
        <a href="deconnexion.php">Se déconnecter</a>
    </div>

</main>
</body>
</html>
