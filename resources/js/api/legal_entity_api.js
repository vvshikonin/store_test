import {defaultAPIInstance} from "./axios_instances";

export const legalEntityAPI = {
    index() {
        const url = '/api/v1/legal_entities';
        return defaultAPIInstance.get(url);
    },
    store() {
        const url = '/api/v1/legal_entities';
        return defaultAPIInstance.post(url);
    },
    update(id, legalEntity){
        const url = '/api/v1/legal_entities/update/' + id;
        return defaultAPIInstance.post(url, legalEntity);
    }
} 