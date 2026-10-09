import { reactive } from 'vue';

const MOBILE_BREAKPOINT = 1024;

const storedSidebar = localStorage.getItem('sidebarOpen');
const storedTheme = localStorage.getItem('darkMode');

const mql = window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);

const initialSidebarOpen = mql.matches
    ? false
    : (storedSidebar !== null ? JSON.parse(storedSidebar) : true);

export const state = reactive({
    sidebarOpen: initialSidebarOpen,
    darkMode: storedTheme !== null ? JSON.parse(storedTheme) : false,
    isMobile: mql.matches,
});

export function setSidebarOpen(value) {
    state.sidebarOpen = value;
    if (!state.isMobile) {
        localStorage.setItem('sidebarOpen', JSON.stringify(value));
    }
}

export function setDarkMode(value) {
    state.darkMode = value;
    localStorage.setItem('darkMode', JSON.stringify(value));
    document.documentElement.classList.toggle('dark', value);
}

function handleViewportChange(e) {
    state.isMobile = e.matches;
    setSidebarOpen(!e.matches); // móvil=>cerrado, escritorio => abierto
}

if (mql.addEventListener) mql.addEventListener('change', handleViewportChange);
else mql.addListener(handleViewportChange); //Safari antiguo

setDarkMode(state.darkMode);