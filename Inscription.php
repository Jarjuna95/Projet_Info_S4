<?php
require_once('./fonctionphp/constantes.inc.php');
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
            $maxId = 0;
            foreach($data as $u) {
                if($u['id'] > $maxId) $maxId = $u['id'];
            }
            $nouvelUtilisateur['id'] = $maxId + 1;
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
        <!-- onsubmit : jd valide tous les champs avant d'envoyer la requête http -->
        <form name="inscription" method="post" action="#" onsubmit="return validerInscription()">
            <fieldset>
                <legend>Inscription</legend>

                <!-- Zone d'affichage des erreurs de validation côté client -->
                <div id="erreurs-inscription" class="message-erreur"></div>

        <div class="div1">Nom</div>
        <div class="div2">
            <input type="text" id="nom" name="nom" class="champ" />
        </div><br />

        <div class="div1">Prénom</div>
        <div class="div2">
            <input type="text" id="prenom" name="prenom" class="champ" />
        </div><br />

        <div class="div1">Adresse</div>
        <div class="div2">
            <input type="text" id="adresse" name="adresse" class="champ" />
        </div><br />

        <div class="div1">Numéro de téléphone</div>
        <div class="div2">
            <input type="tel" id="telephone" name="telephone" class="champ" />
        </div><br />

        <div class="div1">Email</div>
        <div class="div2">
            <input type="email" id="email" name="email" class="champ" />
        </div><br />

        <div class="div1">Mot de passe</div>
        <div class="div2" style="display:flex; align-items:center; gap:8px;">
            <input type="password" id="mdp" name="mdp" class="champ" maxlength="13" onkeyup="compterMdp()" />
            <button type="button" onclick="toggleMdp('mdp', 'oeil-ins')" id="oeil-ins" style="background:none; border:none; cursor:pointer; font-size:22px; padding:5px;">👁 Voir</button>
        </div>
        <div class="div2">
            <span id="compteur-mdp" style="font-size:13px; color:gray;">13 caractères restants</span>
        </div><br />

        <div class="div1"></div>
        <div class="div2">
            <input type="submit" name="sinscrire" value="S'inscrire" class="boutton" />
        </div><br />

            </fieldset>
        </form>
        </div>
        </div>
        <script>
            // Vérifie tous les champs avant d'envoyer le formulaire
            // Retourne false pour bloquer la requête HTTP si un champ est invalide
            function validerInscription() {

                var erreur = '';

                // Lecture des valeurs
                var nom       = document.getElementById('nom').value;
                var prenom    = document.getElementById('prenom').value;
                var email     = document.getElementById('email').value;
                var telephone = document.getElementById('telephone').value;
                var mdp       = document.getElementById('mdp').value;

                if (nom.length === 0) {
                    erreur += 'Le nom est obligatoire.<br>';
                }

                if (prenom.length === 0) {
                    erreur += 'Le prénom est obligatoire.<br>';
                }

                // Email : doit contenir @ et un point 
                if (email.indexOf('@') === -1 || email.indexOf('.') === -1) {
                    erreur += 'Email invalide (doit contenir @ et .).<br>';
                }

                // Téléphone : 10 chiffres et pas de lettres 
                if (telephone.length !== 10 || isNaN(telephone)) {
                    erreur += 'Le téléphone doit contenir exactement 10 chiffres.<br>';
                }

                // Mot de passe : entre 6 et 13 caractères
                if (mdp.length < 6 || mdp.length > 13) {
                    erreur += 'Le mot de passe doit contenir entre 6 et 13 caractères.<br>';
                }

                // Affiche ou efface les erreurs 
                document.getElementById('erreurs-inscription').innerHTML = erreur;

                // return false bloque l'envoi, return true l'autorise 
                return erreur === '';
            }

            // Met à jour le compteur de caractères à chaque frappe 
            function compterMdp() {
                var longueur  = document.getElementById('mdp').value.length; // nb de caractères tapés
                var restants  = 13 - longueur;                               // nb de caractères restants
                var compteur  = document.getElementById('compteur-mdp');

                // Affiche le nombre de caractères restants 
                compteur.textContent = restants + ' caractères restants';

                if (restants <= 3) {
                    compteur.style.color = 'red';    // Proche de la limite la couleur sera rouge
                } else {
                    compteur.style.color = 'gray';   
                }
            }

            // Affiche ou cache le mot de passe 
            function toggleMdp(inputId, btnId) {
                var input = document.getElementById(inputId);
                var btn   = document.getElementById(btnId);
                if (input.type === 'password') {
                    input.type      = 'text';       // Rend le texte visible
                    btn.textContent = '👁 Cacher';
                } else {
                    input.type      = 'password';   // Cache le texte
                    btn.textContent = '👁 Voir';
                }
            }
        </script>
        <script src="script.js"></script>
    </body>
</html>
