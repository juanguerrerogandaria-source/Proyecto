// Al marcar "Cerrado" se deshabilitan las horas de esa fila
document.querySelectorAll('.check-cerrado').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        const fila = checkbox.closest('tr');
        fila.querySelectorAll('input[type="time"]').forEach(function (input) {
            input.disabled = checkbox.checked;
        });
    });
});

// Los formularios con data-confirmar piden confirmación antes de enviarse
document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
    form.addEventListener('submit', function (evento) {
        if (!window.confirm(form.dataset.confirmar)) {
            evento.preventDefault();
        }
    });
});
