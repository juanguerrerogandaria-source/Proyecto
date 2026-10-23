        const btnQuienes    = document.getElementById('btn-quienes-somos');
        const overlayQuienes = document.getElementById('overlay-quienes');
        const cerrarQuienes  = document.getElementById('cerrar-quienes');
        const okQuienes      = document.getElementById('ok-quienes');

        function abrirQuienes() { overlayQuienes.classList.add('overlay--visible'); }
        function cerrarModalQuienes() { overlayQuienes.classList.remove('overlay--visible'); }

        if (btnQuienes) btnQuienes.addEventListener('click', abrirQuienes);
        if (cerrarQuienes) cerrarQuienes.addEventListener('click', cerrarModalQuienes);
        if (okQuienes) okQuienes.addEventListener('click', cerrarModalQuienes);
        if (overlayQuienes) {
            overlayQuienes.addEventListener('click', function (e) {
                if (e.target === overlayQuienes) cerrarModalQuienes();
            });
        }

(function () {
    var toggle = document.getElementById('navbar-toggle');
    var links  = document.getElementById('navbar-links');
    if (!toggle || !links) return;

    toggle.addEventListener('click', function () {
        var abierto = links.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    });

    // Al tocar un link, se cierra el menú
    links.addEventListener('click', function (e) {
        if (e.target.closest('a')) {
            links.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();

(function () {
    var url = new URL(window.location.href);
    if (url.searchParams.has('i')) {
        url.searchParams.delete('i');
        window.history.replaceState(null, '', url.pathname + url.search + url.hash);
    }
})();