import { createApp } from 'vue';
import axios from 'axios';
import AuthApp from './components/auth/AuthApp.vue';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

const el = document.getElementById('auth-app');

createApp(AuthApp, {
    initialView: el.dataset.view || 'login',
    resetToken: el.dataset.token || '',
    resetEmail: el.dataset.email || '',
}).mount(el);
