import { defaultAPIInstance } from "./axios_instances";

export const expenseContragentAPI = {
    async index(params = {}) {
        const url = '/api/v1/expense_contragents';
        return defaultAPIInstance.get(url, { params });
    },
    async show(id) {
        const url = '/api/v1/expense_contragents/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(expenseContragent) {
        const url = '/api/v1/expense_contragents/' + expenseContragent.id;
        return defaultAPIInstance.patch(url, expenseContragent);
    },
    async destroy(expenseContragent) {
        const url = '/api/v1/expense_contragents/' + expenseContragent.id;
        return defaultAPIInstance.delete(url, expenseContragent);
    },
    async store(expenseContragent) {
        const url = '/api/v1/expense_contragents';
        return defaultAPIInstance.post(url, expenseContragent);
    },
}
