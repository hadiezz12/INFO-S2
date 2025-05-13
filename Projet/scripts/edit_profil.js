document.addEventListener('DOMContentLoaded', () => {
    // Stocker les valeurs initiales du formulaire
    const form = document.getElementById('edit-profile-form');
    const originalValues = {
        username: form.querySelector('#username').value,
        bio: form.querySelector('#bio').value,
        profile_picture: form.querySelector('#profile_picture').value
    };

    // Empêcher la soumission du formulaire lors du clic sur #theme-switch
    const themeSwitch = document.getElementById('theme-switch');
    if (themeSwitch) {
        themeSwitch.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
        });
    }

    // Gérer l'édition inline
    document.querySelectorAll('.editable').forEach(element => {
        element.addEventListener('click', () => {
            const field = element.dataset.field;
            const value = element.textContent;
            const formInput = document.getElementById(field);
            let input;
            if (field === 'bio') {
                input = document.createElement('textarea');
                input.rows = 3;
            } else {
                input = document.createElement('input');
                input.type = 'text';
            }
            input.value = value;
            input.className = 'editable-input';
            element.replaceWith(input);
            input.focus();
            input.addEventListener('blur', () => {
                const newValue = input.value.trim();
                const span = document.createElement('span');
                span.className = 'editable';
                span.dataset.field = field;
                span.textContent = newValue || value;
                input.replaceWith(span);
                formInput.value = newValue || value; // Mettre à jour le champ de formulaire
            });
        });
    });

    // Gérer la mise à jour de la photo de profil
    window.updateProfilePicture = function(select) {
        const newValue = select.value;
        const formSelect = document.getElementById('profile_picture');
        const imgContainer = document.querySelector('.profile-pic-container');
        formSelect.value = newValue;
        if (imgContainer) {
            if (newValue) {
                const img = imgContainer.querySelector('img') || document.createElement('img');
                img.src = newValue;
                img.alt = 'Photo de profil';
                img.className = 'edit-profile-current-pic';
                imgContainer.innerHTML = 'Photo actuelle : ';
                imgContainer.appendChild(img);
            } else {
                imgContainer.innerHTML = ''; // Supprimer l'image si aucune photo
            }
        }
    };

    // Réinitialiser le formulaire
    window.resetForm = function() {
        form.querySelector('#username').value = originalValues.username;
        form.querySelector('#bio').value = originalValues.bio;
        form.querySelector('#profile_picture').value = originalValues.profile_picture;
        form.querySelector('.editable[data-field="username"]').textContent = originalValues.username;
        form.querySelector('.editable[data-field="bio"]').textContent = originalValues.bio;
        const imgContainer = document.querySelector('.profile-pic-container');
        if (imgContainer) {
            if (originalValues.profile_picture) {
                const img = imgContainer.querySelector('img') || document.createElement('img');
                img.src = originalValues.profile_picture;
                img.alt = 'Photo de profil';
                img.className = 'edit-profile-current-pic';
                imgContainer.innerHTML = 'Photo actuelle : ';
                imgContainer.appendChild(img);
            } else {
                imgContainer.innerHTML = '';
            }
        }
    };
});