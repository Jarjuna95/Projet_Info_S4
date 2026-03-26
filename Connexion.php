<?php
require_once('./constantes.inc.php');
session_start();

if (isset($_POST['se_connecter'])) {
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];

    if (file_exists(CHEMIN_JSON)) {
        $file = fopen(CHEMIN_JSON, 'r');
        $json = fread($file, 1000000); // On lit une grande taille comme dans ton cours
        fclose($file);
        $data = json_decode($json, True);

        // Boucle FOR pour chercher l'utilisateur
        $identifiants_ok = false;
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['login'] == $email && $data[$i]['password'] == $mdp) {
                $identifiants_ok = true;
                // On stocke le nom de l'utilisateur en session
                $_SESSION[SESSION_LOGIN] = $data[$i]['nom'];
                break;
            }
        }

        if ($identifiants_ok == true) {
            header('Location: ./profil.php');
            exit();
        } else {
            header('Location: ./Connexion.php'); 
            exit();
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
            <a href="Inscription.html" class="creer-compte">Créer un compte</a>
        </div>

    </div>

</body>
</html>
