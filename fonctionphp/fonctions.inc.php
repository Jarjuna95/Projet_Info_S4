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
    fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
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
}
// fin connexion et inscrition
