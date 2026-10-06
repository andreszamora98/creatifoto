import { createApp } from 'vue/dist/vue.esm-bundler.js';
import { createRouter, createWebHistory } from 'vue-router';
import AppLayout from './components/layout/AppLayout.vue';
import OrdersBoard from './components/orders/OrdersBoard.vue';
import QuotesBoard from './components/quotes/QuotesBoard.vue';
import PackagesBoard from './components/packages/PackagesBoard.vue';
import CatalogsBoard from './components/catalog/CatalogsBoard.vue';
import blurOnEnter from './directives/blurOnEnter.js';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: OrdersBoard },
        { path: '/cotizaciones', component: QuotesBoard },
        { path: '/paquetes', component: PackagesBoard },
        { path: '/catalogos', component: CatalogsBoard },
        { path: '/:pathMatch(.*)*', redirect: '/' },
    ],
});

const rootEl = document.getElementById('app');

const app = createApp({
    components: { AppLayout },
    data() {
        return {
            user: {
                userName: rootEl.dataset.userName,
                userEmail: rootEl.dataset.userEmail,
                profilePic: rootEl.dataset.profilePic,
            },
        };
    },
    methods: {
        resetMainScroll() {
            const main = document.querySelector('main');
            if (main) main.scrollTop = 0;
        },
    },
    template: `
        <app-layout
            :user-name="user.userName"
            :user-email="user.userEmail"
            :profile-pic="user.profilePic"
        >
            <router-view v-slot="{ Component }">
                <transition name="page" mode="out-in" @before-enter="resetMainScroll">
                    <component :is="Component" />
                </transition>
            </router-view>
        </app-layout>
    `,
});

app.directive('blur-on-enter', blurOnEnter);
app.use(router);

app.mount('#app');