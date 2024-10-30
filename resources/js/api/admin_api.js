import {defaultAPIInstance} from "./axios_instances";

export const adminAPI = {
    index() {
        const url = '/api/admin';
        return defaultAPIInstance.post(url);
    },
    update(id, data){
        const url = '/api/admin/update/' + id;
        return defaultAPIInstance.post(url, data);
    }
} 