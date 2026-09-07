import { createApp } from 'vue/dist/vue.esm-bundler.js';
import AppLayout from './components/layout/AppLayout.vue';
import OrdersBoard from './components/orders/OrdersBoard.vue';

const app = createApp({
    components: { AppLayout, OrdersBoard },
    template: `
        <AppLayout
            :user-name="'Admin'"
            :user-email="'admin@example.com'"
            :profile-pic="'https://ui-avatars.com/api/?name=Admin&background=random'"
        >
            <OrdersBoard />
        </AppLayout>
    `,
});

app.mount('#app');