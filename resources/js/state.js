import { reactive } from 'vue';

const shouldClose = sessionStorage.getItem('sidebarShouldClose');
if (shouldClose) {
    sessionStorage.removeItem('sidebarShouldClose');
    localStorage.setItem('sidebarOpen', JSON.stringify(false));
}

const storedSidebar = localStorage.getItem('sidebarOpen');
const storedTheme = localStorage.getItem('darkMode');

export const state = reactive({
    sidebarOpen: storedSidebar !== null ? JSON.parse(storedSidebar) : true,
    darkMode: storedTheme !== null ? JSON.parse(storedTheme) : false,
});

export function setSidebarOpen(value) {
    state.sidebarOpen = value;
    localStorage.setItem('sidebarOpen', JSON.stringify(value));
}

export function setDarkMode(value) {
    state.darkMode = value;
    localStorage.setItem('darkMode', JSON.stringify(value));
    if (value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

setDarkMode(state.darkMode);