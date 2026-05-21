<?php
require_once('./fonctionphp/constantes.inc.php');
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
            $_SESSION[SESSION_ROLE]  = $utilisateur['role'];
            $_SESSION[SESSION_ID]    = $utilisateur['id'];
            $_SESSION['nom']         = $utilisateur['nom'];
            $_SESSION['prenom']      = $utilisateur['prenom'];
            if ($utilisateur['role'] === 'livreur') {
                header('Location: ./Livreur.php');
            } elseif ($utilisateur['role'] === 'restaurateur') {
                header('Location: ./Commande.php');
            } elseif ($utilisateur['role'] === 'admin') {
                header('Location: ./admin.php');
            } else {
                header('Location: ./page_accueil_connecte.php');
            }
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
    <link id="css_mode" rel="stylesheet" href="style.css">
</head>

<header id="frontmini">
    <a href="page_accueil.php" class="panierbouton">Accueil</a>
    <h1>La Confrerie</h1>
</header>
 
<body class="fond-image">
    <div class="page-centree">
 
        <div id="conteneur">
            <form name="connexion" method="post" action="#" onsubmit="return validerConnexion()">
                <fieldset>
                    <legend>Connexion</legend>

                    <!-- affiche les erreurs de validation -->
                    <div id="erreurs-connexion" class="message-erreur"></div>

                    <div class="div1">Email</div>
                    <div class="div2">
                        <input type="email" id="email-co" name="email" class="champ" />
                    </div><br />

                    <div class="div1">Mot de passe</div>
                    <div class="div2" style="display:flex; align-items:center; gap:8px;">
                        <input type="password" id="mdp-co" name="mdp" class="champ" maxlength="16" onkeyup="compterMdpCo()" />
                        <button type="button" onclick="toggleMdp('mdp-co', 'oeil-co')" id="oeil-co" style="background:none; border:none; cursor:pointer; font-size:22px; padding:5px;">👁️</button>
                    </div>
                    <div class="div2">
                        <span id="compteur-mdp-co" style="font-size:13px; color:gray;">16 caractères restants</span>
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
 
    <script>
        // Vérifie les champs avant d'envoyer le formulaire
        // Retourne false pour bloquer la requête HTTP si un champ est invalide
        function validerConnexion() {

            var erreur = '';

            // Récupère les valeurs des champs avec getElementById() 
            var email = document.getElementById('email-co').value;
            var mdp   = document.getElementById('mdp-co').value;

            // Email : doit contenir @ et un point 
            if (email.indexOf('@') === -1 || email.indexOf('.') === -1) {
                erreur += 'Email invalide.<br>';
            }

            // Mot de passe doit pas etre vide
            if (mdp.length === 0) {
                erreur += 'Le mot de passe est obligatoire.<br>';
            }

            // Affiche ou efface les erreurs dans le div
            document.getElementById('erreurs-connexion').innerHTML = erreur;

            // return false bloque l'envoi sinon cela return true autorise l'envoie
            return erreur === '';
        }

        // Met à jour le compteur de caractères restants à chaque frappe
        function compterMdpCo() {
            var longueur = document.getElementById('mdp-co').value.length;
            var restants = 16 - longueur;
            var compteur = document.getElementById('compteur-mdp-co');
            compteur.textContent    = restants + ' caractères restants';
           
            // Change la couleur quand il reste plus bcp de caracteres
                if (restants <= 3) {
                    compteur.style.color = 'red';    // Proche de la limite : rouge
                } else {
                    compteur.style.color = 'gray';   // Normal : gris
                }
            }

        // Affiche ou cache le mdp
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
