import { defaultAPIInstance } from "./axios_instances";

export const priceMonitoringAPI = {
    async index(params) {
        const url = '/api/v1/price_monitorings';
        return defaultAPIInstance.get(url, {params});
    },
    async update(id, data) {
        const url = '/api/v1/price_monitorings/' + id;
        return defaultAPIInstance.patch(url, data);
    },
    async destroy(id) {
        const url = '/api/v1/price_monitorings/' + id;
        return defaultAPIInstance.delete(url);
    },
    async store(data) {
        const url = '/api/v1/price_monitorings';
        return defaultAPIInstance.post(url, data);
    },
} 