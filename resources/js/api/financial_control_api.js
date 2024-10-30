import { defaultAPIInstance } from "./axios_instances";

export const financialControlAPI = {
    async index(selection) {
        const url = '/api/v1/financial_controls';
        return defaultAPIInstance.get(url, { params: selection });
    },
    async show(payment_method_id, payment_date) {
        const url = '/api/v1/financial_controls/' + payment_method_id + '/' + payment_date;
        return defaultAPIInstance.get(url);
    },
    async store(financial_control) {
        const url = '/api/v1/financial_controls';
        return defaultAPIInstance.post(url, financial_control);
    },
    update(financial_control) {
        const url = '/api/v1/financial_controls/' + financial_control.id;
        return defaultAPIInstance.put(url, financial_control);
    },
    export(params) {
        const url = '/api/v1/financial_controls/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    export_all_transactions(params) {
        const url = '/api/v1/financial_controls/export_all_transactions';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    import(formData) {
        const url = '/api/v1/financial_controls/import';
        // Обратите внимание: здесь не устанавливаем Content-Type явно
        return defaultAPIInstance.post(url, formData);
    },    
    async destroy(id) {
        const url = '/api/v1/financial_controls/' + id;
        return defaultAPIInstance.delete(url);
    },
} 