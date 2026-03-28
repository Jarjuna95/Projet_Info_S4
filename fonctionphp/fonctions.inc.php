<?php

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

function redirecterSiMauvaisRole($roleAttendu, $url) {
    if (!isset($_SESSION[SESSION_ROLE]) || $_SESSION[SESSION_ROLE] != $roleAttendu) {
        header("Location: $url");
        exit(0);
    }
}

function lireJSON($chemin) {
    if (!file_exists($chemin)) return [];
    $taille = filesize($chemin);
    if ($taille == 0) return [];
    $file = fopen($chemin, 'r');
    if ($file === false) return [];
    $json = fread($file, $taille);
    fclose($file);
    $data = json_decode($json, true);
    return ($data !== null) ? $data : [];
}

function ecrireJSON($chemin, $data) {
    $file = fopen($chemin, 'w');
    if ($file === false) return false;
    fwrite($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fclose($file);
    return true;
}

function lireUtilisateurs() {
    return lireJSON(CHEMIN_UTILISATEURS);
}

function ecrireUtilisateurs($data) {
    return ecrireJSON(CHEMIN_UTILISATEURS, $data);
}

function chercherUtilisateur($data, $login) {
    foreach ($data as $u) {
        if ($u['login'] == $login) return $u;
    }
    return false;
}

function chercherUtilisateurParId($data, $id) {
    foreach ($data as $u) {
        if ($u['id'] == $id) return $u;
    }
    return false;
}

function verifierIdentifiants($data, $login, $mdp) {
    foreach ($data as $u) {
        if ($u['login'] == $login && $u['mot_de_passe'] == $mdp) return $u;
    }
    return false;
}

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


// Retourne toutes les commandes d un client par son id
function commandesDuClient($commandes, $clientId) {
    $res = [];
    foreach ($commandes as $c) {
        if ($c['client_id'] == $clientId) $res[] = $c;
    }
    return $res;
}

// Vérifie si une commande est notable (livrée + pas encore notée)
function estNotable($cmd) {
    return $cmd['statut'] === 'livree' && empty($cmd['note_livraison']);
}
?>
