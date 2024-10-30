import { defaultAPIInstance } from "./axios_instances";

export const expenseTypeAPI = {
    async index() {
        const url = '/api/v1/expense_types';
        return defaultAPIInstance.get(url, {});
    },
    async show(id) {
        const url = '/api/v1/expense_types/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(expenseType) {
        const url = '/api/v1/expense_types/' + expenseType.id;
        return defaultAPIInstance.patch(url, expenseType);
    },
    async destroy(expenseType) {
        const url = '/api/v1/expense_types/' + expenseType.id;
        return defaultAPIInstance.delete(url, expenseType);
    },
    async store(expenseType) {
        const url = '/api/v1/expense_types';
        return defaultAPIInstance.post(url, expenseType);
    },
    async updateOrder(updatedOrder) {
        const url = '/api/v1/expense_types/update_order';
        return defaultAPIInstance.post(url, { updatedOrder });
    }
}
