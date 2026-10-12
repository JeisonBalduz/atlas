document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');

    if (formLogin) {
        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();

            const cedula = document.getElementById('user').value;
            const password = document.getElementById('password').value;

            try {
                const respuesta = await fetch('api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ cedula, password })
                });

                const resultado = await respuesta.json();

                if (respuesta.ok && resultado.status === 'success') {
                    window.location.href = resultado.redirect;
                } else {
                    alert('Error: ' + resultado.mensaje);
                }
            } catch (error) {
                console.error('Falla en la petición:', error);
            }
        });
    }
});