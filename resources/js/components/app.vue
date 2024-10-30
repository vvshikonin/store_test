<template >
    <div class="d-flex flex-row bg-light" style="min-height: 100vh;">
        <template v-if="getToken" class="position-relative">
            <MobileSidebar v-if="isMobile"></MobileSidebar>
            <Sidebar v-else></Sidebar>
        </template>
        <div class="d-flex flex-column h-100 w-100" style="min-height: 100vh; ">
            <router-view :keep-alive="false" :key="$route.fullPath"
                style="margin-left: 20px; margin-right: 20px; border: 1px hidden; z-index: 10; transition: all 0.2s;"></router-view>
            <Footer class="mt-auto"></Footer>
        </div>
    </div>
    <Toaters :toasts="getNotifications"></Toaters>
    <LoadingSpinner v-if="isLoading"></LoadingSpinner>
</template>

<script>
import Sidebar from './Layout/Sidebar/Sidebar.vue';
import MobileSidebar from './Layout/Sidebar/MobileSidebar.vue';
import Footer from './Layout/footer.vue';
import LoadingSpinner from './Layout/loading_spinner.vue';

import Toaters from './popups/toaters.vue';
import { mapGetters } from 'vuex';

export default {
    name: "app",
    components: { Sidebar, MobileSidebar, Footer, LoadingSpinner, Toaters },
    computed: {
        ...mapGetters(['getToken', 'getUserActiveStatus', 'getRoleId', 'getNotifications']),
        isMobile() {
            return window.innerWidth <= 768;
        }
    },
    data() {
        return {
            isLoading: false,
        }
    },
}
</script>
