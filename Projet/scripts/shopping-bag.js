// Requires: window.shoppingBagConfig = {count, link, icon};
// This script should be included at the end of <body> in your PHP pages.

(function() {
    if (!window.shoppingBagConfig) return;
    if (!window.shoppingBagConfig.count || window.location.pathname.includes('panier.php')) return;

    // Remove if already present (avoid duplicates)
    var existing = document.getElementById('shopping-bag-float');
    if (existing) existing.remove();

    var a = document.createElement('a');
    a.href = window.shoppingBagConfig.link;
    a.className = 'shopping-bag-float';
    a.id = 'shopping-bag-float';
    a.title = 'Voir le panier';

    var img = document.createElement('img');
    img.src = window.shoppingBagConfig.icon;
    img.alt = 'Panier';

    a.appendChild(img);

    // Optional: badge with count
    if (window.shoppingBagConfig.count > 0) {
        var badge = document.createElement('span');
        badge.textContent = window.shoppingBagConfig.count;
        badge.style.position = 'absolute';
        badge.style.bottom = '8px';
        badge.style.right = '8px';
        badge.style.background = '#e74c3c';
        badge.style.color = '#fff';
        badge.style.fontSize = '0.95em';
        badge.style.fontWeight = 'bold';
        badge.style.padding = '2px 8px';
        badge.style.borderRadius = '12px';
        badge.style.boxShadow = '0 1px 4px rgba(0,0,0,0.18)';
        a.appendChild(badge);
    }

    document.body.appendChild(a);
})();
