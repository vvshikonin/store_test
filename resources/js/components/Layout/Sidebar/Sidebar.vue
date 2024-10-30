<template>
    <div class="d-flex flex-column vh-100" style="transition: all .2s;" :style="sidebarSizeStyle">
        <SidebarToggleButton @click="$store.dispatch('toggleSidebarMode')"></SidebarToggleButton>
    </div>

    <div class="sidebar d-flex flex-column position-fixed vh-100 shadow-sm" :style="sidebarSizeStyle">
        <SidebarLogo></SidebarLogo>
        <div class="sidebar-scroll p-2">
            <ul v-if="userActiveStatus === 1" class="nav flex-column">
                <SidebarLink permission="product_read" link-text="Склад/Товары" icon="fa-solid fa-boxes-stacked"
                    route="/products"></SidebarLink>
                <SidebarLink permission="price_monitoring_read" link-text="Мониторинг цен"
                    icon="fa-solid fa-money-check-dollar" route="/price_monitoring"></SidebarLink>
                <SidebarLink permission="expenses_read" link-text="Хоз. расходы" icon="fa-solid fa-bag-shopping"
                    route="/expenses">
                </SidebarLink>
                <SidebarLink permission="inventory_read" link-text="Инвентаризации" icon="fa-solid fa-boxes-packing"
                    route="/inventories"></SidebarLink>
                <SidebarLink permission="financial_controls_read" link-text="Контроль расходов"
                    icon="fa-solid fa-cash-register" route="/financial_controls"></SidebarLink>
                <SidebarLink permission="invoice_read" link-text="Счета" icon="fa-solid fa-clipboard" route="/invoices">
                </SidebarLink>
                <SidebarLink permission="product_refund_read" link-text="Возвраты на склад" icon="fa-solid fa-rotate-left"
                    route="/product_refunds"></SidebarLink>
                <SidebarLink permission="contractor_refund_read" link-text="Возвраты поставщику"
                    icon="fa-solid fa-arrow-rotate-right" route="/contractor_refunds"></SidebarLink>
                <SidebarLink permission="defect_read" link-text="Браки" icon="fa-solid fa-box-open" route="/defects">
                </SidebarLink>
                <SidebarLink permission="money_refund_read" link-text="Возвраты ДС" icon="fa-regular fa-credit-card"
                    route="/money-refunds"></SidebarLink>
                <SidebarLink permission="brand_read" link-text="Бренды" icon="fa-solid fa-tag" route="/brands">
                </SidebarLink>
                <SidebarLink permission="contractor_read" link-text="Поставщики" icon="fa-solid fa-truck-fast"
                    route="/contractors"></SidebarLink>
                <SidebarLink permission="legal_entity_read" link-text="Юридические лица" icon="fa-solid fa-user-tie"
                    route="/legal_entities"></SidebarLink>
                <SidebarLink permission="users_managment" link-text="Пользователи" icon="fa-solid fa-user" route="/users">
                </SidebarLink>
                <SidebarLink permission="users_managment" link-text="Группы пользователей" icon="fa-solid fa-users"
                    route="/roles"></SidebarLink>
                <SidebarLink permission="csv-compare" link-text="CSV Compare" icon="fa-solid fa-file-csv"
                    route="/csv-compare"></SidebarLink>
            </ul>
            <ul v-else class="nav flex-column placeholder-glow">
                <SidebarLinkPlaceholder></SidebarLinkPlaceholder>
                <SidebarLinkPlaceholder></SidebarLinkPlaceholder>
                <SidebarLinkPlaceholder></SidebarLinkPlaceholder>
                <SidebarLinkPlaceholder></SidebarLinkPlaceholder>
            </ul>
        </div>
        <div class="mt-auto pt-1 pb-1 p-2">
            <SidebarUserBlock v-if="userActiveStatus === 1" @click="toggleUserPopup()"></SidebarUserBlock>
            <SidebarUserBlockPlaceholder v-else></SidebarUserBlockPlaceholder>
            <div v-if="isShowUserPopup" @click="toggleUserPopup()" class="position-fixed vh-100 vw-100 top-0"></div>
            <Transition>
                <SidebarUserPopup @trigger="isShowUserPopup = false" v-if="isShowUserPopup"></SidebarUserPopup>
            </Transition>
        </div>
    </div>
</template>

<script>

import SidebarLink from './SidebarLink.vue';
import SidebarLogo from './SidebarLogo.vue';
import SidebarToggleButton from '../Sidebar/SidebarToggleButton.vue';
import SidebarUserBlock from '../Sidebar/SidebarUserBlock.vue';
import SidebarUserPopup from '../Sidebar/SidebarUserPopup.vue';
import SidebarLinkPlaceholder from './SidebarLinkPlaceholder.vue';
import SidebarUserBlockPlaceholder from './SidebarUserBlockPlaceholder.vue';
import { mapGetters } from 'vuex';
export default {
    computed: {
        ...mapGetters({ permissions: 'getUserPermissions', userActiveStatus: 'getUserActiveStatus', sidebarMode: 'getSidebarMode' }),
        sidebarSizeStyle() {
            return {
                'min-width': this.sidebarMode ? 'var(--max-sidebar-size)' : 'var(--min-sidebar-size)',
                'max-width': this.sidebarMode ? 'var(--max-sidebar-size)' : 'var(--min-sidebar-size)',
            }
        }
    },
    components: { SidebarLink, SidebarLogo, SidebarToggleButton, SidebarUserBlock, SidebarUserPopup, SidebarLinkPlaceholder, SidebarUserBlockPlaceholder },
    data() {
        return {
            isShowUserPopup: false,
        }
    },
    methods: {
        toggleUserPopup() {
            this.isShowUserPopup = !this.isShowUserPopup;
        },
    },
}
</script>
<style>
.sidebar {
    z-index: 510;
    background-color: #2563eb;
    transition: all .2s;
}

.sidebar-scroll {
    overflow-y: auto;
    overflow-x: hidden;
    height: 800px;
    min-height: 50px;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 5px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background-color: white;
    border-radius: 100px;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background-color: #298dff;
    border-radius: 100px;
}

.sidebar-enter-active {
    transition: all 0.4s ease-out;
}

.sidebar-leave-active {
    opacity: 0.5;
    width: 0px;
}

.sidebar-enter-from,
.sidebar-leave-to {
    opacity: 0;
    width: 0px;
}
</style>
