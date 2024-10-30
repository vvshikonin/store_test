import { defaultAPIInstance } from "./axios_instances";

export const invoiceAPI = {
    async index(selection) {
        const url = '/api/v1/invoices';
        return defaultAPIInstance.get(url, { params: selection });
    },
    async store(invoice) {
        const url = '/api/v1/invoices';
        return defaultAPIInstance.post(url, invoice);
    },
    async show(id) {
        const url = '/api/v1/invoices/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(id, invoice) {
        const url = '/api/v1/invoices/' + id;
        return defaultAPIInstance.post(url, invoice);
    },
    async destroy(id) {
        const url = '/api/v1/invoices/' + id;
        return defaultAPIInstance.delete(url);
    },

    async availableForRefund(invoiceID) {
        const url = '/api/v1/invoices/' + invoiceID + '/products/available-for-refund';
        return defaultAPIInstance.get(url);
    },

    async refunds(invoiceID) {
        const url = '/api/v1/invoices/' + invoiceID + '/refunds';
        return defaultAPIInstance.get(url);
    },

    async bulkUpdate(params) {
        const url = '/api/v1/invoices/bulk/update';
        return defaultAPIInstance.put(url, params);
    },
    async bulkDestroy(params) {
        const url = '/api/v1/invoices/bulk/delete';
        return defaultAPIInstance.post(url, params);
    },

    async export(params) {
        const url = '/api/v1/invoices/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async exportProducts(params) {
        const url = '/api/v1/invoices/products/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },

    async exportForReceive(params) {
        const url = '/api/v1/invoices/products/export/for-receive';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },

    async exportForControl(params) {
        const url = '/api/v1/invoices/products/export/for-control';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    }

}
