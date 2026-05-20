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