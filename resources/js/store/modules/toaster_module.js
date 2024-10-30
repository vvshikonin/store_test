export const notificationModule = {

    state: {
        notifications: []
    },
    getters: {
        getNotifications: (state) => state.notifications,
    },
    mutations: {
        ADD_NOTIFICATION(state, notification) {
            state.notifications.push(notification)
        },
    },
    actions: {
        addNotification({ commit }, notification) {
            commit('ADD_NOTIFICATION', notification)
        }
    }
}
