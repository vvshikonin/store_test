<template>
    <div>
        <div class="d-flex flex-row align-items-center  position-fixed pt-3 pb-3 bg-white shadow-sm"
            style="right: 0; height: 80px; z-index: 1000; transition: all .2s;"
            :style="{ 'left': sidebarMode ? 'var(--max-sidebar-size)' : 'var(--min-sidebar-size)' }">
            <div>
                <h1 class="ms-4 pe-3 ">
                    Prof-TE Склад
                </h1>
            </div>
            <div v-if="userName" @click="showUserMenu()"
                class="user-menu ms-auto d-flex flex-row align-items-center border-start ps-3">
                <UserAvatar :avatar="userAvatar" :userName="userName" :userColor="userColor"></UserAvatar>
                <div class="d-flex flex-column ms-3 pt-2 pb-2 me-3">
                    <span>{{ userName }}</span>
                    <small class="text-muted">{{ userEmail }}</small>
                    <small v-if="userRoles"> {{ userRoles[0] }}</small>
                </div>
            </div>
            <div v-else class="user-menu ms-auto border-start d-flex flex-row align-items-center ps-3 placeholder-glow"
                style="min-width: 300px; padding: .55rem; cursor:wait;">
                <UserAvatar userName=" " userColor="100,100,100" class="placeholder"></UserAvatar>
                <div class="d-flex flex-column ms-3 pt-2 pb-2 me-3 w-75" style="font-size: 11px;">
                    <span class=" placeholder mb-2 rounded text-nowrap w-50"></span>
                    <small class="placeholder text-muted mb-2 rounded w-100"></small>
                    <small class="placeholder rounded w-75"></small>
                </div>
            </div>
        </div>

        <div v-if="!permissions.length" class="d-flex justify-content-center align-items-center m-5 p-5 vh-100">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <ProductSearch v-else style="margin-top: 100px"></ProductSearch>
    </div>
</template>
<style>
.user-menu:hover {
    background-color: aliceblue;
    cursor: pointer;
}



.home-search-table {
    max-height: 600px;
    overflow-y: auto;
}

.home-search-table::-webkit-scrollbar {
    width: 5px;
}

.home-search-table::-webkit-scrollbar-track {
    background-color: white;
    border-radius: 100px;
}

.home-search-table::-webkit-scrollbar-thumb {
    background-color: #298dff;
    border-radius: 100px;
}
</style>
<script>

import { productAPI } from '../api/products_api';
import { mapGetters } from 'vuex';

import ProductSearch from '../modules/Products/ProductSearch';

import IndexTableMixin from '../utils/indexTableMixin.js'

import TextInput from '../components/inputs/default_input.vue';
import UserAvatar from '../components/Other/user_avatar.vue';
import Card from '../components/Layout/card.vue';
import OrdersTablePopup from '../components/popups/orders_table_popup.vue'
export default {
    data() {
        return {
            productSearchText: '',
            productSearchResult: [],
            productSearchEntries: false,
            productSearchTableSettings: {
                isLoading: false,
                isNoEntries: false,
                withFooter: false,
            }
        }
    },
    mixins: [IndexTableMixin],
    components: { Card, UserAvatar, TextInput, OrdersTablePopup, ProductSearch },
    computed: {
        ...mapGetters({
            permissions: 'getUserPermissions', userName: 'getUserName', userEmail: 'getUserEmail', userAvatar: 'getUserAvatar',
            userRoles: 'getUserRoles', userActiveStatus: 'getUserActiveStatus', userColor: 'getUserColor', sidebarMode: 'getSidebarMode'
        }),
        showStoreProductCard() {
            return this.checkPermission('product_read') || this.checkPermission('product_create')
        },
        showInventoryCard() {
            return this.checkPermission('inventory_read') || this.checkPermission('inventory_create')
        },
        showInvoiceCard() {
            return this.checkPermission('invoice_read') || this.checkPermission('invoice_create')
        },
        showBrandCard() {
            return this.checkPermission('brand_read') || this.checkPermission('brand_create')
        },
        showContractorCard() {
            return this.checkPermission('contractor_read') || this.checkPermission('contractor_create')
        }
    },
    methods: {
        showUserMenu() {
            this.$router.push('/my_profile');
        },
        async productSearch(text) {
            this.settings.isLoading = true;
            this.productSearchEntries = true;
            this.productSearchTableSettings.isLoading = true;
            await productAPI.homeSearch(text).then((res) => {
                this.productSearchResult = res.data.data;
                if (!res.data.data.length) {
                    this.settings.isNoEntries = true;
                } else {
                    this.settings.isNoEntries = false;
                }

            })
            this.settings.isLoading = false;

        },
        onClearSearch() {
            this.productSearchEntries = false;
            this.productSearchResult = [];
            this.productSearchText = '';
        },
        initSettings() {
            this.settings.withCreateButton = false;
            this.settings.withHeader = false;
            this.settings.withTitle = false;
            this.settings.withExport = false;
            this.settings.isLoading = false;
            this.settings.saveParams = false;
            this.settings.withBottomBox = false;
            this.settings.withFilters = false;
            this.settings.withFooter = false;
            this.settings.withFooter = false;
            this.settings.withPagination = false;
            this.settings.indexAPI = null;
            this.settings.isLoading = false;
        },
    },
    mounted() {
        this.$store.dispatch('loadUserData');
        if (this.userActiveStatus === 0) {
            this.$router.push('/forbidden');
        }
    }
}
</script>
