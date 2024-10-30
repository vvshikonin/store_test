import { defaultAPIInstance } from "./axios_instances";

export const expenseAPI = {
    async index(selection, noPaginate = false) {
        const url = '/api/v1/expenses';
        const params = { ...selection };
        if (noPaginate) {
            params.no_paginate = 'true';
        }
        return defaultAPIInstance.get(url, { params });
    },
    async show(id) {
        const url = '/api/v1/expenses/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(expense) {
        const url = '/api/v1/expenses/' + expense.id;
        return defaultAPIInstance.patch(url, expense);
    },
    async destroy(expense) {
        const url = '/api/v1/expenses/' + expense.id;
        return defaultAPIInstance.delete(url, expense);
    },
    async store(expense) {
        const url = '/api/v1/expenses';
        return defaultAPIInstance.post(url, expense);
    },
    async createFastExpense(file) {
        const url = '/api/v1/expenses/fast';
        return defaultAPIInstance.post(url, file);
    },
    async convertToMoneyRefund(expense) {
        const url = '/api/v1/expenses/convert-to-money-refund';
        return defaultAPIInstance.post(url, expense);
    },
    // async loadFiles(data, id) {
    //     const url ='/api/v1/expenses/' + id + '/files';
    //     return defaultAPIInstance.post(url, data);
    // },
    // async deleteFile(link, id) {
    //     const url ='/api/v1/expenses/expense-delete-file/' + id;
    //     return defaultAPIInstance.post(url, { link: link });
    // },
    // Метод для загрузки одного файла
    async uploadFile(data, id) {
        const url = `/api/v1/expenses/${id}/file`;
        return defaultAPIInstance.post(url, data, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },
    async uploadInvoiceFile(data, id) {
        const url = `/api/v1/expenses/${id}/invoice-file`;
        return defaultAPIInstance.post(url, data, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },
    // Метод для удаления файла
    async deleteFile(id) {
        const url = `/api/v1/expenses/${id}/file`;
        return defaultAPIInstance.delete(url);
    },
    async deleteInvoiceFile(id) {
        const url = `/api/v1/expenses/${id}/invoice-file`;
        return defaultAPIInstance.delete(url);
    },
    async expense_items_export(params) {
        const url = '/api/v1/expenses/expense-items-export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async expense_sorted_types_export(params) {
        const url = '/api/v1/expenses/expense-sorted-types-export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
}
