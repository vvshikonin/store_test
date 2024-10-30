<template>
    <li title="Загрузка">
        <a class="nav-link d-flex rounded-1 placeholder bg-transparent" :class="linkJustifyClass">
            <div class="d-flex justify-content-center align-items-center" style="width: 30px; height: 30px;">
                <font-awesome-icon class="text-white" icon="fa-solid fa-circle" size="lg" />
            </div>
            <Transition name="sidebar">
                <div v-if="sidebarMode" class="d-flex align-items-center w-100">
                    <small class="ms-3 text-nowrap text-white placeholder rounded w-100"></small>
                </div>
            </Transition>
        </a>
    </li>
</template>
<style>
.sidebar .nav-link {
    margin-top: 0.3rem;
    transition: all 0.3s;
}

.sidebar .placeholder.nav-link:hover {
    background-image: var(--bs-gradient);
    background-color: #3b82f6;
}

.sidebar .router-link-active.nav-link {
    background-color: #1d4ed8;
}
</style>
<script>
import { mapGetters } from 'vuex';
export default {
    computed: {
        ...mapGetters({ sidebarMode: 'getSidebarMode' }),
        linkJustifyClass() {
            return this.sidebarMode ? 'justify-content-start' : 'justify-content-center';
        }
    }
}


</script>