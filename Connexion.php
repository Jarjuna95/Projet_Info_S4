<?php
require_once('./constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');

session_start();

redirecterSiConnecte('./profil.php'); // Si l utilisateur est deja connecte on le redirige vers son profil

$erreur = "";

if (isset($_POST['se_connecter'])) {
    if (!isset($_POST['email']) || !isset($_POST['mdp'])) {
        header('Location: ./Connexion.php');
        exit(0);
    }

    $email = $_POST['email'];
    $mdp   = $_POST['mdp'];

    $data = lireUtilisateurs();

    if ($data === false) {
        $erreur = "Erreur interne : impossible de lire les données.";
    } else {
        // Vérification des identifiants
        $utilisateur = verifierIdentifiants($data, $email, $mdp);

        if ($utilisateur !== false) {
            $_SESSION[SESSION_LOGIN] = $utilisateur['login'];
            $_SESSION['role']        = $utilisateur['role'];
            $_SESSION['nom']         = $utilisateur['nom'];
            $_SESSION['prenom']      = $utilisateur['prenom'];
            header('Location: ./profil.php');
            exit(0);
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Page de Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="fond-image">
    <div class="page-centree">

        <div id="conteneur">
            <form name="connexion" method="post" action="#">
                <fieldset>
                    <legend>Connexion</legend>

                    <div class="div1">Email</div>
                    <div class="div2">
                        <input type="email" name="email" class="champ" required />
                    </div><br />
                    
                    <div class="div1">Mot de passe</div>
                    <div class="div2">
                        <input type="password" name="mdp" class="champ" required />
                    </div><br />

                    <div class="div1"></div>
                    <div class="div2">
                        <input type="submit" name="se_connecter" value="Se connecter" class="boutton" />
                    </div><br />

                </fieldset>
            </form>
            <a href="Inscription.php" class="creer-compte">Créer un compte</a>
        </div>

    </div>

</body>
</html>
