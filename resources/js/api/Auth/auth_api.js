import { loginAPIInstance } from "../axios_instances";
import { defaultAPIInstance } from "../axios_instances";

export const authAPI = {

    login(email, password){
        const url = '/api/auth/login';
        const data = {email, password};
        return loginAPIInstance.post(url, data);
    },

    logout(){
        const url = '/api/auth/logout';
        return defaultAPIInstance.post(url);
    },
    me(){
        const url = '/api/auth/me';
        return defaultAPIInstance.post(url);
    },
    register(name, email, password, passwordConfirmation){
        const url = '/api/auth/register';
        const data = {name, email, password, passwordConfirmation}
        return loginAPIInstance.post(url, data);
    }
} 