import { defaultAPIInstance } from "./axios_instances";

export const userFilterTemplateAPI = {
    async index(params) {
        const url = '/api/v1/user-filter-templates';
        return defaultAPIInstance.get(url, { params });
    },
    async store(params){
        const url = '/api/v1/user-filter-templates';
        return defaultAPIInstance.post(url, params);
    },
    async update(id, name){
        const url = '/api/v1/user-filter-templates/' + id;
        return defaultAPIInstance.put(url, { name: name });
    },
    async delete(id){
        const url = '/api/v1/user-filter-templates/' + id;
        return defaultAPIInstance.delete(url);
    }
}