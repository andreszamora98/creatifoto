import { createApp } from 'vue/dist/vue.esm-bundler.js';
import AppLayout from './components/layout/AppLayout.vue';
import OrdersBoard from './components/orders/OrdersBoard.vue';
import QuotesBoard from './components/quotes/QuotesBoard.vue';
import PackagesBoard from './components/packages/PackagesBoard.vue';
import CatalogsBoard from './components/catalog/CatalogsBoard.vue';
import blurOnEnter from './directives/blurOnEnter.js';


const app = createApp({
    components: { AppLayout, OrdersBoard, QuotesBoard, PackagesBoard, CatalogsBoard },
});
app.directive('blur-on-enter', blurOnEnter);

app.mount('#app');