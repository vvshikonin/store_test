import {defaultAPIInstance} from "./axios_instances";

export const moneyRefundAPI = {
    async index(params) {
        const url = '/api/v1/money_refundables';
        return defaultAPIInstance.get(url, {params});
    },
    async show(id) {
        const url = '/api/v1/money_refundables/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(id, refund) {
        const url = '/api/v1/money_refundables/' + id;
        return defaultAPIInstance.put(url, refund);
    },
    async create(refund) {
        const url = '/api/v1/money_refundables';
        return defaultAPIInstance.post(url, refund);
    },
    async uploadDocFile(id, data) {
        const url = '/api/v1/money_refundables/upload-doc-file/' + id;
        return defaultAPIInstance.post(url, data);
    },
    async store(request) {
        const url = '/api/v1/money_refundables';
        return defaultAPIInstance.post(url, request);
    },
    async export(params) {
        const url = '/api/v1/money_refundables/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async exportIncomes(params) {
        const url = '/api/v1/money_refundables/export-incomes';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async exportProducts(params) {
        const url = '/api/v1/money_refundables/export-products';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async convertToExpense(id) {
        const url = '/api/v1/money_refundables/convert-to-expense/' + id;
        return defaultAPIInstance.post(url);
    }
} 