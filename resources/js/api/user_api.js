import { defaultAPIInstance } from "./axios_instances";

export const userAPI = {
    async index(params) {
        const url = '/api/v1/users';
        return defaultAPIInstance.get(url, { params });
    },
    async show(id){
        const url = '/api/v1/users/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(id, user){
        const url = '/api/v1/users/' + id;
        return defaultAPIInstance.post(url, user);
    },
    async delete_avatar(id){
        const url = '/api/v1/users/' + id + '/delete_avatar';
        return defaultAPIInstance.post(url);
    }
}