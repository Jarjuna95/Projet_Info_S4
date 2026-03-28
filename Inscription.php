<?php
require_once('./constantes.inc.php');
require_once('./fonctionphp/fonctions.inc.php');

session_start();
redirecterSiConnecte('./profil.php');
$erreur = "";

if (isset($_POST['sinscrire'])) {

    $nom     = $_POST['nom'];
    $prenom  = $_POST['prenom'];
    $email   = $_POST['email'];
    $adresse = $_POST['adresse'];
    $tel     = $_POST['telephone'];
    $mdp     = $_POST['mdp'];

    if ($nom == "" || $prenom == "" || $email == "" || $mdp == "") { //verif des champs obligatoire
        $erreur = "Veuillez remplir tous les champs obligatoires.";

    } else {

        $data = lireUtilisateurs();
        if ($data === false) {
            $data = [];
        }

        $existeDeja = chercherUtilisateur($data, $email);

        if ($existeDeja !== false) {
            $erreur = "Un compte existe déjà avec cet email.";

        } else {
            $nouvelUtilisateur = [];
            $nouvelUtilisateur['id']               = count($data) + 1;
            $nouvelUtilisateur['login']            = $email;
            $nouvelUtilisateur['mot_de_passe']         = $mdp;
            $nouvelUtilisateur['role']             = "client";
            $nouvelUtilisateur['nom']              = $nom;
            $nouvelUtilisateur['prenom']           = $prenom;
            $nouvelUtilisateur['adresse']          = $adresse;
            $nouvelUtilisateur['telephone']        = $tel;
            $nouvelUtilisateur['date_inscription'] = date('Y-m-d');
            $nouvelUtilisateur['statut']           = "actif";
            $nouvelUtilisateur['points_fidelite']  = 0;

            $data[] = $nouvelUtilisateur;

            $ok = ecrireUtilisateurs($data);
            if ($ok) {
                header('Location: ./Connexion.php');
                exit(0);
            } else {
                $erreur = "Erreur interne : impossible d'enregistrer l'utilisateur.";
            }
        }
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
            <input type="password" name="mdp" class="champ" />
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
