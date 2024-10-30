import {defaultAPIInstance} from "./axios_instances";

export const orderStatusAPI = {
    async index() {
        const url = '/api/v1/order-statuses';
        return defaultAPIInstance.get(url);
    },
}