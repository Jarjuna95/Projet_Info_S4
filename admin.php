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
                <?php for ($i = 0; $i < count($utilisateurs); $i++): ?>
                <tr>
                    <td><?php echo $utilisateurs[$i]['nom']; ?></td>
                    <td><?php echo $utilisateurs[$i]['prenom']; ?></td>
                    <td><?php echo $utilisateurs[$i]['login']; ?></td>
                    <td><?php echo $utilisateurs[$i]['date_inscription']; ?></td>
                    <td><?php echo ucfirst($utilisateurs[$i]['statut']); ?></td>
                    <td>
                        <a href="" class="boutton"> //mettre lien
                            Voir le profil
                        </a>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </section>
</body>
</html>
