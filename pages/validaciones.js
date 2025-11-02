function soloLetras(event) {
    const char = event.key;
    const letras = /^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/;

    if (
        char.length === 1 &&
        !letras.test(char)
    ) {
        event.preventDefault();
    }
}

// Espera a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    const campos = [
        'nombreAlumno',
        'apellidoAlumno',
        'nombreDocente',
        'apellidoDocente',
        'nombreAdscripto',
        'apellidoAdscripto'
    ];

    campos.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) {
            campo.addEventListener('keypress', soloLetras);
        }
    });
});