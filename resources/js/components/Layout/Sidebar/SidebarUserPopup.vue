<template>
    <div class="popup position-fixed bg-white border rounded-1 p-2" :style="popupStyle">
        <div class="d-flex flex-row align-items-center border-bottom pb-2">
            <div class="d-flex flex-column" style="width: 185px;">
                <span class="d-inline-block text-truncate" :title="userName">{{ userName }}</span>
                <small class="d-inline-block text-truncate" :title="userEmail">{{ userEmail }}</small>
            </div>
            <UserAvatar :avatar="userAvatar" :userColor="userColor" :userName="userName" class="me-2"></UserAvatar>
        </div>
        <div @click="toUserProfile" class="popup-menu p-2 mt-1 rounded-1">
            <font-awesome-icon icon="fa-solid fa-user" /> Профиль
        </div>
        <div @click="toAppSettings" class="popup-menu p-2 mt-1 rounded-1">
            <font-awesome-icon icon="fa-solid fa-gear" /> Настройки
        </div>
        <div @click="onLogout" class="popup-menu p-2 mt-1 rounded-1">
            <font-awesome-icon icon="fa-solid fa-right-from-bracket" /> Выход
        </div>
    </div>
</template>
<style>
.sidebar .popup {
    bottom: 5px;
    width: 250px;
    transition: all 0.2s;
}

.sidebar .popup-menu:hover {
    background-color: aliceblue;
    cursor: pointer;
}
</style>
<script>
import { mapGetters } from 'vuex';
import UserAvatar from '../../Other/user_avatar.vue';
export default {
    components: { UserAvatar },
    emits: ['trigger'],
    methods: {
        onLogout() {
            this.$store.dispatch('onLogout');
        },
        toUserProfile() {
            this.$emit('trigger');
            this.$router.push('/my_profile');
        },
        toAppSettings() {
            this.$emit('trigger');
            this.$router.push('/settings');
        }
    },
    computed: {
        ...mapGetters({ userColor: 'getUserColor', userAvatar: 'getUserAvatar', userName: 'getUserName', userEmail: 'getUserEmail', sidebarMode: 'getSidebarMode' }),
        popupStyle() {
            return { "left": this.sidebarMode ? "calc(var(--max-sidebar-size) + .5rem)" : "calc(var(--min-sidebar-size) + .5rem)" }
        }
    }
}
</script>