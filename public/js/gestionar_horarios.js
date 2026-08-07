document.querySelectorAll('.check-cerrado').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const fila = checkbox.closest('tr');
                fila.querySelectorAll('input[type="time"]').forEach(function (input) {
                    input.disabled = checkbox.checked;
                });
            });
        });