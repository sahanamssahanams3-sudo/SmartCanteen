document.addEventListener("DOMContentLoaded", function () {

    const savedTheme = localStorage.getItem("canteenTheme");

    if (savedTheme) {
        document.body.className = savedTheme;
    }
});

function setTheme(theme) {
    document.body.className = theme;

    localStorage.setItem("canteenTheme", theme);
}