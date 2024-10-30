import {defaultAPIInstance} from "./axios_instances";

export const productRefundAPI = {
    async index(params) {
        const url = '/api/v1/product_refunds';
        return defaultAPIInstance.get(url, {params});
    },
    async show(id) {
        const url = '/api/v1/product_refunds/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(id, refund){
        const url = '/api/v1/product_refunds/' + id;
        return defaultAPIInstance.post(url, refund);
    },
    async export(params) {
        const url = '/api/v1/product_refunds/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async exportProducts(params) {
        const url = '/api/v1/product_refunds/export-products';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    }

} 