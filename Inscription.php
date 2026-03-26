<?php
require_once('./constantes.inc.php');

// On vérifie si le bouton du formulaire a été cliqué
if (isset($_POST['sinscrire'])) {
    
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];

    $data = [];

    // Lecture du fichier (si il existe)
    if (file_exists(CHEMIN_JSON)) {
        $file = fopen(CHEMIN_JSON, 'r');
        $size = filesize(CHEMIN_JSON);
        // Si le fichier n'est pas vide on lit, sinon tableau vide
        $json = ($size > 0) ? fread($file, $size) : "[]";
        fclose($file);
        $data = json_decode($json, True);
    }

    // Vérification si l'utilisateur existe déjà avec une boucle FOR
    $trouve = false;
    for ($i = 0; $i < count($data); $i++) {
        if ($data[$i]['login'] == $email) {
            $trouve = true;
            break;
        }
    }

    if ($trouve == true) {
        echo "Erreur : cet utilisateur existe déjà !";
    } else {
        // On ajoute le nouvel utilisateur au tableau
        $data[] = [
            "nom" => $nom,
            "prenom" => $prenom,
            "login" => $email,
            "password" => $mdp
        ];

        // Écriture dans le fichier
        $file = fopen(CHEMIN_JSON, 'w');
        fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
        fclose($file);

        header('Location: ./Connexion.php');
        exit();
    }
}
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Page d'inscription</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="fond-image">

        <div class="page-centree">
        <div id="conteneur">
        <form name="inscription" method="post" action="#">
            <fieldset>
                <legend>Inscription</legend>

        <div class="div1">Nom</div>
        <div class="div2">
            <input type="text" name="nom" class="champ" />
        </div><br />

        <div class="div1">Prénom</div>
        <div class="div2">
            <input type="text" name="prenom" class="champ" />
        </div><br />

        <div class="div1">Adresse</div>
        <div class="div2">
            <input type="text" name="adresse" class="champ" />
        </div><br />

        <div class="div1">Numéro de téléphone</div>
        <div class="div2">
            <input type="tel" name="telephone" class="champ" />
        </div><br />

        <div class="div1">Email</div>
        <div class="div2">
            <input type="email" name="email" class="champ" />
        </div><br />
        
        <div class="div1">Mot de passe</div>
        <div class="div2">
            <input type="Mot de passe" name="mdp" class="champ" />
        </div><br />

        <div class="div1"></div>
        <div class="div2">
            <input type="submit" name="sinscrire" value="S'inscrire" class="boutton" />
        </div><br />

            </fieldset>
        </form>
        </div>
        </div>
    </body>
</html>
