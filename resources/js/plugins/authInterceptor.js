import axios from 'axios';

/**
 * Si la sesión expira (401) o el token CSRF deja de ser válido (419),
 * manda al usuario al login en lugar de dejar la SPA con errores sueltos.
 * Esto es solo comodidad: la protección real está en el middleware 'auth' del servidor.
 */
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let redirecting = false;

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;

        if ((status === 401 || status === 419) && !redirecting) {
            redirecting = true;
            window.location.href = '/login';
        }

        return Promise.reject(error);
    }
);
