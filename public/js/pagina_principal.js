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