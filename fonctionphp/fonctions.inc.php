<?php
//connexion et inscription
function redirecterSiConnecte($url) {
    if (isset($_SESSION[SESSION_LOGIN]) && $_SESSION[SESSION_LOGIN] != "") {
        header("Location: $url");
        exit(0);
    }
}

function redirecterSiNonConnecte($url) {
    if (!isset($_SESSION[SESSION_LOGIN]) || $_SESSION[SESSION_LOGIN] == "") {
        header("Location: $url");
        exit(0);
    }
}

function lireUtilisateurs() {
    if (!file_exists(CHEMIN_JSON)) {
        return false;
    }

    $file = fopen(CHEMIN_JSON, 'r');
    if ($file == false) {
        return false;
    }

    $taille = filesize(CHEMIN_JSON);
    if ($taille > 0) {
        $json = fread($file, $taille);
    } else {
        $json = "[]";
    }
    fclose($file);

    return json_decode($json, true);
}

function ecrireUtilisateurs($data) {
    $file = fopen(CHEMIN_JSON, 'w');
    if ($file == false) {
        return false;
    }
    fwrite($file, json_encode($data));
    fclose($file);
    return true;
}

function chercherUtilisateur($data, $login) {
    for ($i = 0; $i < count($data); $i++) {
        if ($data[$i]['login'] == $login) {
            return $data[$i];
        }
    }
    return false;
}

function verifierIdentifiants($data, $login, $mdp) {
    for ($i = 0; $i < count($data); $i++) {
        if ($data[$i]['login'] == $login && $data[$i]['mot_de_passe'] == $mdp) {
            return $data[$i];
        }
    }
    return false;
// fin connexion et inscrition


    //commande 
    function lireCommandes() {
    return lireJSON(CHEMIN_COMMANDES);
}

function ecrireCommandes($data) {
    return ecrireJSON(CHEMIN_COMMANDES, $data);
}

function commandesDuLivreur($commandes, $livreurId) {
    $res = [];
    foreach ($commandes as $c) {
        if ($c['livreur_id'] == $livreurId) $res[] = $c;
    }
    return $res;
}

function chercherCommandeParId($commandes, $id) {
    foreach ($commandes as $c) {
        if ($c['id'] == $id) return $c;
    }
    return false;
}

function mettreAJourStatutCommande($commandeId, $nouveauStatut) {
    $commandes = lireCommandes();
    $ok = false;
    for ($i = 0; $i < count($commandes); $i++) {
        if ($commandes[$i]['id'] == $commandeId) {
            $commandes[$i]['statut'] = $nouveauStatut;
            $ok = true;
            break;
        }
    }
    if ($ok) ecrireCommandes($commandes);
    return $ok;
}


function lirePlats() {
    return lireJSON(CHEMIN_PLATS);
}

function chercherPlatParId($plats, $id) {
    foreach ($plats as $p) {
        if ($p['id'] == $id) return $p;
    }
    return false;
}

?>
}
