console.log("main.js chargé avec succès");

// Fonction pour obtenir la valeur d'un cookie
function getCookie(name) {
    try {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    } catch (error) {
        console.error("Erreur lors de l'accès aux cookies :", error);
        return null;
    }
}

// Fonction pour définir un cookie
function setCookie(name, value, days) {
    try {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/`;
        console.log(`Cookie ${name} défini à : ${value}`);
    } catch (error) {
        console.error("Erreur lors de la définition du cookie :", error);
    }
}

// Fonction pour gérer les images de fond
function updateBackgroundImages(theme) {
    const darkImage = document.querySelector('.background-image.dark-image');
    const lightImage = document.querySelector('.background-image.light-image');
    if (darkImage && lightImage) {
        if (theme === 'dark') {
            darkImage.style.display = 'block';
            darkImage.style.opacity = '1';
            lightImage.style.display = 'none';
            lightImage.style.opacity = '0';
        } else {
            darkImage.style.display = 'none';
            darkImage.style.opacity = '0';
            lightImage.style.display = 'block';
            lightImage.style.opacity = '1';
        }
    }
}

// Appliquer le thème immédiatement
(function() {
    const theme = getCookie('theme') || 'dark';
    const cssLink = document.getElementById('theme-css');
    if (cssLink) {
        const cssPath = `/Projet/css/${theme === 'dark' ? 'styles' : 'light'}.css?v=${new Date().getTime()}`;
        cssLink.href = cssPath;
        console.log('Nouveau chemin CSS :', cssPath);
        setCookie('theme', theme, 30);
        updateBackgroundImages(theme);
        // Vérifier le chargement du CSS
        fetch(cssPath)
            .then(response => {
                if (!response.ok) {
                    console.error(`Erreur de chargement du fichier CSS : ${cssPath} (Statut : ${response.status})`);
                    const fallbackPath = `/css/${theme === 'dark' ? 'styles' : 'light'}.css?v=${new Date().getTime()}`;
                    cssLink.href = fallbackPath;
                    console.log('Tentative avec chemin de secours :', fallbackPath);
                }
            })
            .catch(error => {
                console.error(`Erreur réseau lors du chargement du CSS : ${cssPath}`, error);
            });
    } else {
        console.error('Erreur : #theme-css non trouvé dans le DOM');
    }
})();

// Gérer le basculement de thème
document.addEventListener('DOMContentLoaded', () => {
    const themeSwitch = document.getElementById('theme-switch');
    if (themeSwitch) {
        const theme = getCookie('theme') || 'dark';
        if (theme === 'light') {
            themeSwitch.classList.add('light');
            document.documentElement.classList.add('light');
        }
        themeSwitch.addEventListener('click', () => {
            console.log('Clic détecté sur #theme-switch');
            const currentTheme = getCookie('theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            console.log('Changement de thème vers :', newTheme);
            const cssLink = document.getElementById('theme-css');
            if (cssLink) {
                const cssPath = `/Projet/css/${newTheme === 'dark' ? 'styles' : 'light'}.css?v=${new Date().getTime()}`;
                cssLink.href = cssPath;
                console.log('Nouveau chemin CSS :', cssPath);
                setCookie('theme', newTheme, 30);
                themeSwitch.classList.toggle('light');
                document.documentElement.classList.toggle('light');
                updateBackgroundImages(newTheme);
                // Vérifier le chargement du CSS
                fetch(cssPath)
                    .then(response => {
                        if (!response.ok) {
                            console.error(`Erreur de chargement du fichier CSS : ${cssPath} (Statut : ${response.status})`);
                            const fallbackPath = `/css/${newTheme === 'dark' ? 'styles' : 'light'}.css?v=${new Date().getTime()}`;
                            cssLink.href = fallbackPath;
                            console.log('Tentative avec chemin de secours :', fallbackPath);
                        }
                    })
                    .catch(error => {
                        console.error(`Erreur réseau lors du chargement du CSS : ${cssPath}`, error);
                    });
            }
        });
    } else {
        console.log('Aucun #theme-switch trouvé (normal pour certaines pages)');
    }
});