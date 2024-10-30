import { authAPI } from "../../api/Auth/auth_api";
import { userAPI } from "../../api/user_api";
export const userModule = {
    state() {
        return {
            id: null,
            name: null,
            email: null,
            activeStatus: null,
            color: null,
            avatar: null,
            roles: [],
            permissions: []
        }
    },

    getters: {
        getUserName(state)
        {
            return state.name;
        },
        getUserEmail(state)
        {
            return state.email;
        },
        getUserRoles(state)
        {
            return state.roles;
        },
        getUserPermissions(state)
        {
            return state.permissions;
        },
        getUserActiveStatus(state)
        {
            return state.activeStatus;
        },
        getUserColor(state)
        {
            return state.color;
        },
        getUserAvatar(state)
        {
            return state.avatar;
        },
        getUserID(state)
        {
            return state.id;
        },
        getUserData(state)
        {
            return state;
        }
    },

    mutations: {
        setUserData(state, userData)
        {
            state.id = userData.id;
            state.name = userData.name;
            state.email = userData.email;
            state.activeStatus = userData.is_active;
            state.roles = userData.roles;
            state.permissions = userData.permissions;
            state.color = userData.color;
            state.avatar = userData.avatar;
        }
    },

    actions: {
        async loadUserData({ commit }) {
            return authAPI.me().then((res) => {
                commit('setUserData', res.data);
            })
        },
        async updateUserData({ commit }, { userId, formData }) {
            return userAPI.update(userId, formData).then((res) => {
                commit('setUserData', res.data.data);
            })
        }
    }
}