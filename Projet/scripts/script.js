window.addEventListener("DOMContentLoaded", function () {
    let navbar = document.querySelector(".navbar");

    // Vérifie si on est sur la page d'accueil
    if (document.body.id === "home-page") {
        window.addEventListener("scroll", function () {
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    } else {
        // Pour les autres pages, la navbar a un fond fixe
        navbar.classList.add("scrolled");
    }
}); 