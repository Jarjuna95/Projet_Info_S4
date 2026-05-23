function swap() {
    var lnk = document.getElementById('css_mode');
    if (lnk.href.includes("darkstyle.css")) {
        lnk.href = "./style.css";
        document.cookie = "mode=light; max-age=31536000";
    } else {
        lnk.href = "./darkstyle.css";
        document.cookie = "mode=dark; max-age=31536000";
    }
}

var cookies = document.cookie;
if (cookies.includes("mode=dark")) {
    document.getElementById('css_mode').href = "./darkstyle.css";
}

function filtrer(option) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            afficherPlats(JSON.parse(xhr.responseText));
        }
    };
    xhr.open("GET", "filtre.php?categorie=" + option.getAttribute('data-categorie'), true);
    xhr.send();
}

function afficherPlats(plats) {
    var grille = document.getElementById('grille-produits');
    grille.innerHTML = '';
    plats.forEach(function(plat) {
        grille.innerHTML +=
            '<div class="carte-produit">'
            + '<img src="' + plat.image + '" alt="' + plat.nom + '">'
            + '<h3>' + plat.nom + '</h3>'
            + '<p>' + plat.description + '</p>'
            + '<span>' + plat.prix + ' €</span>'
            + '<form method="post" action="presentation.php">'
            + '<input type="hidden" name="plat_id" value="' + plat.id + '">'
            + '<button type="submit" class="bouttonpanier">Ajouter au panier</button>'
            + '</form>'
            + '</div>';
    });
}

function enregistrerCommande(commandeId) {
    var statut   = document.getElementById('select-statut').value;
    var livreur  = document.getElementById('select-livreur').value;
 
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('message-commande').innerHTML = xhr.responseText;
        }
    };
    xhr.open("POST", "update_commande.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("commande_id=" + commandeId + "&nouveau_statut=" + statut + "&livreur_id=" + livreur);
}
