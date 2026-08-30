import { reactive } from 'vue';

// Leer desde localStorage
const storedSidebar = localStorage.getItem('sidebarOpen');
const storedTheme = localStorage.getItem('darkMode');

export const state = reactive({
    sidebarOpen: storedSidebar !== null ? JSON.parse(storedSidebar) : true,
    darkMode: storedTheme !== null ? JSON.parse(storedTheme) : false,
});

// Funciones para actualizar y persistir
export function setSidebarOpen(value) {
    state.sidebarOpen = value;
    localStorage.setItem('sidebarOpen', JSON.stringify(value));
}

export function setDarkMode(value) {
    state.darkMode = value;
    localStorage.setItem('darkMode', JSON.stringify(value));
    // Aplicar clase al HTML
    if (value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Inicializar tema
setDarkMode(state.darkMode);