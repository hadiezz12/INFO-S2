document.addEventListener('DOMContentLoaded', function () {
    const resultsList = document.getElementById('results-list');
    const sortSelect = document.getElementById('sort-select');
    const sortOrderBtn = document.getElementById('sort-order-btn');
    if (!resultsList || !sortSelect || !sortOrderBtn) return;

    // Correction : le tri doit rester sur le filtre choisi même après reload
    // On mémorise le filtre et l'ordre dans localStorage et on restaure AVANT le tri initial
    let ascending = localStorage.getItem('search_sort_order') === 'desc' ? false : true;
    if (localStorage.getItem('search_sort_by')) {
        sortSelect.value = localStorage.getItem('search_sort_by');
    }
    sortOrderBtn.textContent = ascending ? '▲' : '▼';

    function parseDuree(duree) {
        if (!duree) return 0;
        const match = duree.match(/(\d+)/);
        return match ? parseInt(match[1], 10) : 0;
    }

    function sortResults() {
        const items = Array.from(resultsList.children);
        const sortBy = sortSelect.value;
        items.sort((a, b) => {
            let va, vb;
            if (sortBy === 'date') {
                va = new Date(a.dataset.date);
                vb = new Date(b.dataset.date);
            } else if (sortBy === 'prix') {
                va = parseFloat(a.dataset.prix);
                vb = parseFloat(b.dataset.prix);
            } else if (sortBy === 'duree') {
                va = parseDuree(a.dataset.duree);
                vb = parseDuree(b.dataset.duree);
            } else if (sortBy === 'pays') {
                va = (a.dataset.pays || '').toLowerCase();
                vb = (b.dataset.pays || '').toLowerCase();
            }
            if (va < vb) return ascending ? -1 : 1;
            if (va > vb) return ascending ? 1 : -1;
            return 0;
        });
        items.forEach(item => resultsList.appendChild(item));
    }

    sortSelect.addEventListener('change', function () {
        localStorage.setItem('search_sort_by', sortSelect.value);
        sortResults();
    });
    sortOrderBtn.addEventListener('click', function () {
        ascending = !ascending;
        sortOrderBtn.textContent = ascending ? '▲' : '▼';
        localStorage.setItem('search_sort_order', ascending ? 'asc' : 'desc');
        sortResults();
    });

    // Tri initial après restauration du filtre
    sortResults();
});
