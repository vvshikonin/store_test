import { defaultAPIInstance } from "./axios_instances";

export const contractorRefundAPI = {
    async store(refund){
        const url = '/api/v1/contractor_refunds';
        return defaultAPIInstance.post(url, refund);
    },

    async index(params){
        const url = '/api/v1/contractor_refunds';
        return defaultAPIInstance.get(url, {params});
    },

    async show(id){
        const url = '/api/v1/contractor_refunds/' + id;
        return defaultAPIInstance.get(url);
    },

    async update(refund, id){
        const url = '/api/v1/contractor_refunds/' + id;
        return defaultAPIInstance.post(url, refund);
    },

    async destroy(id){
        const url = '/api/v1/contractor_refunds/' + id;
        return defaultAPIInstance.delete(url);
    },

    async export(params) {
        const url = '/api/v1/contractor_refunds/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },

    async exportProducts(params) {
        const url = '/api/v1/contractor_refunds/export_products';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    }
}
