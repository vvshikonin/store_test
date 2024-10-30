<template>
    <div class="sidebar-user-block d-flex p-2 rounded-1 placeholder-glow" :class="justifyClass" title="Загрузка" style="cursor: wait;">
        <UserAvatar userColor="255,255,255" userName=" " :bgOpacity="'1'" class="bg-gradient border-0 placeholder" style="min-width: 45px; min-height: 45px;">
        </UserAvatar>
        <Transition name="sidebar">
            <div v-if="sidebarMode" class="d-flex flex-column ms-3  text-white w-100" style="max-width: 190px;">
                <span class="d-inline-block text-truncate placeholder mb-2 mt-1 rounded w-75" ></span>
                <small class="d-inline-block text-truncate placeholder rounded w-100"></small>
            </div>
        </Transition>
    </div>
</template>
<style>
.sidebar-user-block {
    cursor: pointer;
    transition: all 0.2s;

}

.sidebar-user-block:hover {
    background-color: #1d4ed8;
}
</style>
<script>
import { mapGetters } from 'vuex';
import UserAvatar from '../../Other/user_avatar.vue';
export default {
    components: { UserAvatar },
    computed: {
        ...mapGetters({sidebarMode: 'getSidebarMode' }),
        justifyClass() {
            return this.sidebarMode ? 'justify-content-start ps-3' : 'justify-content-center';
        }
    }
}
</script>
