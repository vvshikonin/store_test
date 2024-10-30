<template>
    <li v-if="isShowLink" :title="linkText">
        <router-link :to="route" class="nav-link d-flex text-white rounded-1" :class="linkJustifyClass">
            <div class="d-flex justify-content-center align-items-center" style="width: 30px; height: 28px;">
                <font-awesome-icon :icon="icon" size="lg" />
            </div>
            <Transition name="sidebar">
                <div v-if="sidebarMode" class="d-flex align-items-center">
                    <span class="ps-3 text-nowrap">{{ linkText }}</span>
                </div>
            </Transition>
        </router-link>
    </li>
</template>
<style>
.sidebar .nav-link,
.mobile-sidebar .nav-link {
    margin-top: 0.3rem;
    transition: all 0.3s;
}

.sidebar .nav-link:hover,
.sidebar .nav-link:focus,
.mobile-sidebar .nav-link:hover,
.mobile-sidebar .nav-link:focus {
    background-image: var(--bs-gradient);
    background-color: #3b82f6;
}

.sidebar .router-link-active.nav-link,
.mobile-sidebar .router-link-active.nav-link {
    background-color: #1d4ed8;
}
</style>
<script>
import { mapGetters } from 'vuex';
export default {
    props: {
        permission: {
            type: String,
            default: null,
        },
        linkText: {
            type: String,
            default: 'linkText prop'
        },
        icon: {
            type: String,
            default: 'fa-solid fa-image'
        },
        route: {
            type: String,
            default: '/'
        },
    },
    computed: {
        ...mapGetters({ sidebarMode: 'getSidebarMode' }),
        isShowLink() {
            return this.permission === null || this.checkPermission(this.permission)
        },
        linkJustifyClass() {
            return this.sidebarMode ? 'justify-content-start' : 'justify-content-center';
        }
    }
}


</script>