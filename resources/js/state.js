import { reactive } from 'vue';

const MOBILE_BREAKPOINT = 1024;

const storedSidebar = localStorage.getItem('sidebarOpen');
const storedTheme = localStorage.getItem('darkMode');

const mql = window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);
const hoverMql = window.matchMedia('(hover: hover) and (pointer: fine)');

const initialSidebarOpen = mql.matches
    ? false
    : (storedSidebar !== null ? JSON.parse(storedSidebar) : true);

export const state = reactive({
    sidebarOpen: initialSidebarOpen,
    darkMode: storedTheme !== null ? JSON.parse(storedTheme) : false,
    isMobile: mql.matches,
    hasHover: hoverMql.matches,
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
    setSidebarOpen(!e.matches);
}

const mqlWithLegacy = /** @type {MediaQueryList & { addListener?: Function }} */ (mql);
const hoverMqlWithLegacy = /** @type {MediaQueryList & { addListener?: Function }} */ (hoverMql);

if (mql.addEventListener) mql.addEventListener('change', handleViewportChange);
else mqlWithLegacy.addListener?.(handleViewportChange);

if (hoverMql.addEventListener) {
    hoverMql.addEventListener('change', (e) => { state.hasHover = e.matches; });
} else {
    hoverMqlWithLegacy.addListener?.((e) => { state.hasHover = e.matches; });
}

setDarkMode(state.darkMode);