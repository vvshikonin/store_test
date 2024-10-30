<template>
    <div class="sidebar-toggle-button position-fixed shadow d-flex justify-content-center bg-white align-items-center rounded-circle text-muted "
        :style="leftPositionStyle" :title="titelText">
        <font-awesome-icon size="xs" icon="fa-solid fa-chevron-right" class="opacity-75" :rotation="iconRotation"
            style="transition: all 0.3s;" fixed-width />
    </div>
</template>
<style>
.sidebar-toggle-button {
    width: 22px;
    height: 22px;
    top: 0rem;
    transition: all 0.2s;
    z-index: 1000;
    cursor: pointer;
    border: 0.5px solid white;
}

.sidebar-toggle-button:hover {
    background-color: #edf4ff !important;
    border-color: var(--bs-blue) !important;
    color: var(--bs-blue) !important;
}
</style>
<script>
import { mapGetters } from 'vuex';

export default {
    computed: {
        ...mapGetters({ sidebarMode: 'getSidebarMode' }),
        iconRotation() {
            return this.sidebarMode ? 180 : 0
        },
        titelText() {
            return this.sidebarMode ? "Свернуть меню" : "Развернуть меню"
        },
        leftPositionStyle() {
            return {
                'left': this.sidebarMode ? 'calc( var(--max-sidebar-size) - .9rem)' : 'calc( var(--min-sidebar-size) - .9rem)'
            }
        }
    }
}
</script>