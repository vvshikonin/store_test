import { defaultAPIInstance } from "../../api/axios_instances";
import { authAPI } from "../../api/Auth/auth_api";
import {router} from '../../routes.js'

export const authModule = {
    state(){
        return{
            credentials: {
                token: localStorage.getItem('token') || null,
                userId: null,
            }
        }
    },

    getters:{
        getToken:(state) => state.credentials.token,
    },

    mutations:{
        setToken(state, token){
            localStorage.setItem('token', token);
        },
        setUserId(state, uId){

        },
        deleteToken(state){
            localStorage.removeItem('token');
        }
    },

    actions:{
        async onLogin({commit}, {email, password}){
            return authAPI.login(email, password).then((res)=>{
                commit('setToken', res.data.access_token);
                defaultAPIInstance.defaults.headers['authorization'] = `Bearer ${res.data.access_token}`;
            })
        },
        onLogout({commit}){
            commit('deleteToken');
            delete defaultAPIInstance.defaults.headers['authorization'];
            window.location.href = '';
        },
        async onSingUp({commit}, {name, email, password, passwordConfirmation}){
            return authAPI.register(name, email, password, passwordConfirmation).then((res) => {

            })
        }
    }
}