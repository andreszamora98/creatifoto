import { createApp } from 'vue/dist/vue.esm-bundler.js';
import AppLayout from './components/layout/AppLayout.vue';
import OrdersBoard from './components/orders/OrdersBoard.vue';
import QuotesBoard from './components/quotes/QuotesBoard.vue';


const app = createApp({
    components: { AppLayout, OrdersBoard, QuotesBoard },
});

app.mount('#app');