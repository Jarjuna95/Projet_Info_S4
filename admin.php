<?php
require_once('./fonctionphp/constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');
session_start();
redirecterSiNonConnecte('./Connexion.php');
redirecterSiMauvaisRole('admin', './Connexion.php');
 
$utilisateurs = lireUtilisateurs();
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Page Administrateur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="accueil2">
    <section>
        <h1 class="ptitre">────── Administration ──────</h1>

        <div class="rechercherplat">
             <input type="text" class="recherchep" placeholder="Rechercher un utilisateur...">
            <button class="boutton">Rechercher</button>
        </div>
         

        <table class="tableau">
        <thead>
            <tr> <th>Nom</th> <th>Prénom</th> <th>Mail</th> <th>Date d'inscription</th> <th>Statut</th> <th>Profil</th> </tr>
        </thead>
        <tbody>
            <?php foreach($utilisateurs as $u): ?>
            <tr>
                <td><?php echo $u['nom']; ?></td>
                <td><?php echo $u['prenom']; ?></td>
                <td><?php echo $u['login']; ?></td>
                <td><?php echo $u['date_inscription']; ?></td>
                <td><?php echo ucfirst($u['statut']); ?></td>
                <td><a href="profil_version_admin.php?id=<?php echo $u['id']; ?>" class="boutton">Voir le profil</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </section>
    <div class="lien-deconnexion">
        <a href="deconnexion.php" class="boutton">🚪 Se déconnecter</a>
    </div>
</body>
</html>
