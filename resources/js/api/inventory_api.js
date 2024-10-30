import {defaultAPIInstance} from "./axios_instances";

export const inventoryAPI = {
    async index(params) {
        const url = '/api/v1/inventories';
        return defaultAPIInstance.get(url, {params});
    },
    async show(id) {
        const url = '/api/v1/inventories/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(id, data){
        const url = '/api/v1/inventories/' + id;
        return defaultAPIInstance.patch(url, data);
    },
    async store(){
        const url = '/api/v1/inventories';
        return defaultAPIInstance.post(url);
    },

    async correct(data){
        const url = '/api/v1/inventories/correct';
        return defaultAPIInstance.put(url, data);
    },


    async export(params) {
        const url = '/api/v1/inventories/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async completedExport(params) {
        const url = '/api/v1/inventories/completed-export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },

    async destroy(id) {
        const url = '/api/v1/inventories/' + id;
        return defaultAPIInstance.delete(url);
    },
}
