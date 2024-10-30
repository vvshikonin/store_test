<template>
    <router-link to="/" class="sidebar-logo d-flex m-2 mt-3 rounded-1 text-decoration-none" :style="routerLinkBGColorStyle">
        <div class="d-flex align-items-center p-2 rounded-1" style="transition: all 0.2s;" :style="iconConteinerStyles"
            :class="iconConteinerClasses">
            <font-awesome-icon class="text-white" icon="fa-solid fa-dolly" size="2x" />
        </div>
        <Transition name="sidebar">
            <span v-if="sidebarMode" class="ps-1 fw-bolder text-white d-flex align-items-center text-nowrap">
                Prof-TE Склад
            </span>
        </Transition>
    </router-link>
</template>
<style>
.sidebar-logo:hover {
    background-image: var(--bs-gradient);
}
</style>
<script>
import { mapGetters } from 'vuex';

export default {
    computed: {
        ...mapGetters({ sidebarMode: 'getSidebarMode' }),
        routerLinkBGColorStyle() {
            return { 'background-color': this.sidebarMode ? '#3b82f6' : '#3b82f6' }
        },
        iconConteinerStyles() {
            return { 'background-color': this.sidebarMode ? '#60a5fa' : 'transparent' }
        },
        iconConteinerClasses() {
            return this.sidebarMode ? 'justify-content-start m-2' : 'justify-content-center w-100'
        }
    }
}
</script>