import { defaultAPIInstance } from "./axios_instances";

export const expenseSummaryAPI = {
    async index(selection) {
        const url = '/api/v1/expense-summaries';
        return defaultAPIInstance.get(url, { params: selection });
    },
    async show(id, legalEntityFilter) {
        const url = '/api/v1/expense-summaries/' + id;
        return defaultAPIInstance.get(url, { params: legalEntityFilter });
    },
    async update(id, totalIncome) {
        const url = '/api/v1/expense-summaries/' + id;
        return defaultAPIInstance.patch(url, { total_income: totalIncome });
    },
    async generate() {
        const url = '/api/v1/expense-summaries/generate';
        return defaultAPIInstance.post(url);
    },
    async exportRegular(params) {
        const url = '/api/v1/expense-summaries/export/regular';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async exportDetailed(params) {
        const url = '/api/v1/expense-summaries/export/detailed';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    }
};