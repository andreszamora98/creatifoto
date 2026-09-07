import { reactive } from 'vue';

export const store = reactive({
    pageTitle: 'CreatiFoto',
});

export function setPageTitle(title) {
    store.pageTitle = title;
}