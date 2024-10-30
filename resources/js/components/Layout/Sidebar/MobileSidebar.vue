<template>
    <!-- <button type="button" class="hamburger" @click="toggleSidebar" :class="{ 'active': isSidebarOpen }">
      <span></span>
      <span></span>
      <span></span>
    </button> -->
    <div class="mobile-sidebar" ref="sidebar" :class="{ 'open': isSidebarOpen }">
        <button type="button" class="hamburger" @click="toggleSidebar" :class="{ 'active': isSidebarOpen }">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div v-if="isSidebarOpen">
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
                    <SidebarLink permission="product_refund_read" link-text="Возвраты на склад"
                        icon="fa-solid fa-rotate-left" route="/product_refunds"></SidebarLink>
                    <SidebarLink permission="contractor_refund_read" link-text="Возвраты поставщику"
                        icon="fa-solid fa-arrow-rotate-right" route="/contractor_refunds"></SidebarLink>
                    <SidebarLink permission="defect_read" link-text="Браки" icon="fa-solid fa-box-open" route="/defects">
                    </SidebarLink>
                    <SidebarLink permission="money_refund_read" link-text="Возвраты ДС" icon="fa-regular fa-credit-card"
                        route="/money_refunds"></SidebarLink>
                    <SidebarLink permission="brand_read" link-text="Бренды" icon="fa-solid fa-tag" route="/brands">
                    </SidebarLink>
                    <SidebarLink permission="contractor_read" link-text="Поставщики" icon="fa-solid fa-truck-fast"
                        route="/contractors"></SidebarLink>
                    <SidebarLink permission="legal_entity_read" link-text="Юридические лица" icon="fa-solid fa-user-tie"
                        route="/legal_entities"></SidebarLink>
                    <SidebarLink permission="users_managment" link-text="Пользователи" icon="fa-solid fa-user"
                        route="/users">
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
            <div class="p-2 pt-1 position-relative">
                <SidebarUserBlock v-if="userActiveStatus === 1" @click="toggleUserPopup()"></SidebarUserBlock>
                <SidebarUserBlockPlaceholder v-else></SidebarUserBlockPlaceholder>
                <!-- <div v-if="isShowUserPopup" @click="toggleUserPopup()" class="position-fixed bottom-0"></div> -->
                <Transition>
                    <SidebarUserPopup style="top: 65vh" @trigger="isShowUserPopup = false" v-if="isShowUserPopup">
                    </SidebarUserPopup>
                </Transition>
            </div>
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
        ...mapGetters({ permissions: 'getUserPermissions', userActiveStatus: 'getUserActiveStatus' }),
    },
    components: { SidebarLink, SidebarLogo, SidebarToggleButton, SidebarUserBlock, SidebarUserPopup, SidebarLinkPlaceholder, SidebarUserBlockPlaceholder },
    data() {
        return {
            isShowUserPopup: false,
            isSidebarOpen: false,
        }
    },
    methods: {
        toggleUserPopup() {
            this.isShowUserPopup = !this.isShowUserPopup;
        },
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },
        handleOutsideClick(event) {
            if (!this.$refs.sidebar.contains(event.target)) {
                this.isSidebarOpen = false;
            }
        }
    },
    mounted() {
        document.addEventListener('click', this.handleOutsideClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleOutsideClick);
    }
}
</script>

<style scoped>
.hamburger {
    /* background-color: white;*/
    padding-top: 10px;
    padding-left: 12px;
    /* border-radius: 50px; */
    border: none;
    background: none;
    cursor: pointer;
    outline: none;
    transition: all .3s ease;
}

.hamburger span {
    display: block;
    width: 25px;
    height: 3px;
    margin: 5px 0;
    background: white;
    transition: all 0.3s ease;
}

.hamburger.active {
    padding-top: 17px;
    padding-left: 27px;
}

.hamburger.active span:nth-child(1) {
    transform: rotate(45deg) translate(6px, 6px);
}

.hamburger.active span:nth-child(2) {
    opacity: 0;
}

.hamburger.active span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -5px);
}

.mobile-sidebar {
    position: fixed;
    top: 10px;
    left: 10px;
    width: 50px;
    height: 50px;
    z-index: 10001;
    border-radius: 12px;
    background-color: #2563eb;
    border: 1px solid #2563eb;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    clip-path: circle(40px at 25px 25px);
}

.mobile-sidebar.open {
    width: 80px;
    height: 87vh;
    transition: all 0.3s ease;
    clip-path: circle(100%);
}

.sidebar-scroll {
    overflow-y: auto;
    overflow-x: hidden;
    height: 65vh;
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
</style>
