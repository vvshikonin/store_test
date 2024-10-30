// Импорт библиотек
import { createRouter, createWebHashHistory } from 'vue-router';

// Импорт Компонентов
import login from './components/Auth/login.vue';
import register from './components/Auth/registration.vue';
import forgotPassword from './components/Auth/forgot-password.vue';
import moneyRefundsPage from './pages/MoneyRefunds.vue';
import moneyRefundPage from './pages/MoneyRefund.vue';
import defectPage from './pages/defect.vue';
import defectsPage from './pages/defects.vue';
import productRefundsPage from './pages/product_refunds.vue';;
import productRefundPage from './pages/product_refund.vue';
import product from './pages/product.vue';
import PriceMonitoring from './pages/PriceMonitoring.vue';
import inventoriesPage from './pages/inventories.vue';
import inventoryPage from './pages/inventory.vue';
import invoicesPage from './pages/invoices.vue';
import invoicePage from './pages/invoice.vue';
import brandsPage from './pages/brands.vue';
import contractorsPage from './pages/contractors.vue';
import contractorPage from './pages/contractor.vue';
import productsPage from './pages/products.vue';
import PageNotFound from './pages/404.vue';
import Forbidden from './pages/forbidden.vue';
import usersPage from './pages/users.vue';
import userPage from './pages/user.vue';
import userProfile from './pages/user_profile.vue';
import rolesPage from './pages/roles.vue';
import rolePage from './pages/role.vue';
import homePage from './pages/home.vue';
import legalEntities from './pages/LegalEntities.vue';
import financialControls from './pages/financial_controls.vue';
import ContractorRefunds from './pages/contractor_refunds.vue';
import ContractorRefund from './pages/contractor_refund.vue';
import expensesPage from './pages/expenses.vue';
import expensePage from './pages/expense.vue';
import expenseSummary from './pages/expense_summary.vue';
import csv_compare from './pages/csv_compare.vue';
import settingsPage from './pages/settings.vue';

import { store } from './store/store';

// Защита авторизации
const isAuthorized = localStorage.hasOwnProperty('token');

const authGuard = function (to, from, next) {
    if (!isAuthorized) next({ name: 'login' });
    else next()
}

function permissionGuard(permissionName) {
    return async function (to, from, next) {

        if (!isAuthorized) {
            next({ name: 'login' });
        }

        await store.dispatch('loadUserData');

        if (store.getters.getUserPermissions.includes(permissionName) && store.getters.getUserActiveStatus === 1) {
            next();
        }
        else {
            next({ path: '/forbidden' });
        };
    }
}

//Редирект с логина если пользователь авторизован
const redirectFromLogin = function (to, from, next) {
    if (isAuthorized && (to.name == 'login' || to.name == 'register')) next({ name: from.name });
    else next()
}

// Создание роутов
const routes = [
    {
        path: '/',
        name: 'home',
        component: homePage,
        beforeEnter: authGuard,
    },
    {
        path: '/inventories',
        name: 'inventories',
        component: inventoriesPage,
        beforeEnter: permissionGuard('inventory_read'),
    },
    {
        path: '/inventories/new',
        component: inventoryPage,
        beforeEnter: permissionGuard('inventory_create'),
    },
    {
        path: '/inventories/:inventory_id/edit',
        component: inventoryPage,
        beforeEnter: permissionGuard('inventory_read'),
    },
    {
        path: '/registration',
        name: 'register',
        component: register,
        beforeEnter: redirectFromLogin,
    },
    {
        path: '/login',
        name: 'login',
        component: login,
        beforeEnter: redirectFromLogin,
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: forgotPassword,
    },
    {
        path: '/products',
        name: 'products',
        component: productsPage,
        beforeEnter: permissionGuard('product_read'),
    },
    {
        path: '/invoices',
        name: 'invoices',
        component: invoicesPage,
        beforeEnter: permissionGuard('invoice_read'),
    },
    {
        path: '/invoices/:invoice_id/edit',
        component: invoicePage,
        beforeEnter: permissionGuard('invoice_read'),
    },
    {
        path: '/invoices/new',
        component: invoicePage,
        beforeEnter: permissionGuard('invoice_create'),
    },
    {
        path: '/invoices/copy',
        component: invoicePage,
        beforeEnter: permissionGuard('invoice_create'),
    },
    {
        path: '/money-refunds',
        name: 'money_refunds',
        component: moneyRefundsPage,
        beforeEnter: permissionGuard('money_refund_read'),
    },
    {
        path: '/money-refunds/:money_refund_id/edit',
        component: moneyRefundPage,
        beforeEnter: permissionGuard('money_refund_read'),
    },
    {
        path: '/defects',
        name: 'defects',
        component: defectsPage,
        beforeEnter: permissionGuard('defect_read'),
    },
    {
        path: '/defects/:defect_id/edit',
        component: defectPage,
        beforeEnter: permissionGuard('defect_read'),
    },

    {
        path: '/contractor_refunds',
        name: 'contractor_refunds',
        component: ContractorRefunds,
        beforeEnter: permissionGuard('contractor_refund_read'),
    },
    {
        path: '/contractor_refunds/:contractor_refund_id/edit',
        component: ContractorRefund,
        beforeEnter: permissionGuard('contractor_refund_read'),
    },
    {
        path: '/product_refunds',
        name: 'product_refunds',
        component: productRefundsPage,
        beforeEnter: permissionGuard('product_refund_read'),
    },
    {
        path: '/product_refunds/:product_refund_id/edit',
        component: productRefundPage,
        beforeEnter: permissionGuard('product_refund_read'),
    },
    {
        path: '/price_monitoring',
        component: PriceMonitoring,
        beforeEnter: permissionGuard('price_monitoring_read'),
    },
    {
        path: '/products/:product_id/edit',
        component: product,
        beforeEnter: permissionGuard('product_show'),
    },
    {
        path: '/products/new',
        component: product,
        beforeEnter: permissionGuard('product_create'),
    },
    {
        path: '/brands/',
        component: brandsPage,
        beforeEnter: permissionGuard('brand_read'),
    },
    {
        path: '/contractors/',
        component: contractorsPage,
        beforeEnter: permissionGuard('contractor_read'),
    },
    {
        path: '/contractors/new',
        component: contractorPage,
        beforeEnter: permissionGuard('contractor_create'),
    },
    {
        path: '/contractors/:contractor_id/edit',
        component: contractorPage,
        beforeEnter: permissionGuard('contractor_read'),
    },
    {
        path: '/users/',
        component: usersPage,
        beforeEnter: permissionGuard('users_managment'),
    },
    {
        path: '/users/:user_id/edit',
        component: userPage,
        beforeEnter: permissionGuard('users_managment'),
    },
    {
        path: '/my_profile',
        component: userProfile,
        beforeEnter: authGuard,
    },
    {
        path: '/settings',
        component: settingsPage,
        beforeEnter: authGuard,
    },
    {
        path: '/roles/',
        component: rolesPage,
        beforeEnter: permissionGuard('users_managment'),
    },
    {
        path: '/roles/new',
        component: rolePage,
        beforeEnter: permissionGuard('users_managment'),
    },
    {
        path: '/roles/:role_id/edit',
        component: rolePage,
        beforeEnter: permissionGuard('users_managment'),
    },
    {
        path: '/legal_entities',
        component: legalEntities,
        beforeEnter: permissionGuard('legal_entity_read'),
    },
    {
        path: '/financial_controls',
        component: financialControls,
        beforeEnter: permissionGuard('financial_controls_read'),
    },
    {
        path: '/404',
        component: PageNotFound,
        beforeEnter: authGuard,
    },
    {
        path: '/forbidden',
        component: Forbidden,
        beforeEnter: authGuard,
    },
    {
        path: '/:catchAll(.*)',
        component: PageNotFound,
        beforeEnter: authGuard,
    },
    {
        path: '/expenses',
        name: 'expenses',
        component: expensesPage,
        beforeEnter: permissionGuard('expenses_read'),
    },
    {
        path: '/expenses/:expense_id/edit',
        component: expensePage,
        beforeEnter: permissionGuard('expenses_read'),
    },
    {
        path: '/expenses/new',
        component: expensePage,
        beforeEnter: permissionGuard('expenses_create'),
    },
    {
        path: '/expense-summary',
        component: expenseSummary,
        beforeEnter: permissionGuard('expenses_read'),
    },
    {
        path: '/csv-compare',
        name: 'csv_compare',
        component: csv_compare,
        beforeEnter: permissionGuard('expenses_create'),
    },
]

// Инициализация роутов
export const router = createRouter({
    history: createWebHashHistory(),
    routes,
});
