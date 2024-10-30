<template>
    <div class="sidebar-user-block d-flex p-2 rounded-1" :class="justifyClass" style="">
        <UserAvatar :avatar="userAvatar" :userColor="userColor" :userName="userName" :bgOpacity="'1'"
            :class="userAvatar ? '' : 'bg-gradient'" class="border-0" style="min-width: 45px; min-height: 45px;">
        </UserAvatar>
        <Transition name="sidebar">
            <div v-if="sidebarMode" class="d-flex flex-column ms-3  text-white" style="max-width: 190px;">
                <span class="d-inline-block text-truncate" :title="userName">{{ userName }}</span>
                <small class="d-inline-block text-truncate" :title="userEmail">{{ userEmail }}</small>
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
        ...mapGetters({ userColor: 'getUserColor', userAvatar: 'getUserAvatar', userName: 'getUserName', userEmail: 'getUserEmail', sidebarMode: 'getSidebarMode' }),
        justifyClass() {
            return this.sidebarMode ? 'justify-content-start ps-3' : 'justify-content-center';
        }
    }
}
</script>
