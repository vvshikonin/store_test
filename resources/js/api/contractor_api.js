import { defaultAPIInstance } from "./axios_instances";

export const contractorAPI = {
    async index(params) {
        const url = '/api/v1/contractors';
        return defaultAPIInstance.get(url, {params});
    },
    async show(id) {
        const url = '/api/v1/contractors/' + id;
        return defaultAPIInstance.get(url);
    },
    async update(contractor, codes) {
        const url = '/api/v1/contractors/' + contractor.id;
        return defaultAPIInstance.patch(url, contractor, {codes});
    },
    async destroy(id) {
        const url = '/api/v1/contractors/' + id;
        return defaultAPIInstance.delete(url);
    },
    async store(contractor) {
        const url = '/api/v1/contractors';
        return defaultAPIInstance.post(url, contractor);
    },
    async export(params) {
        const url = '/api/v1/contractors/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async checkSymbolicCode(code) {
        const url = '/api/v1/contractors/check_symbolic_code';
        return defaultAPIInstance.post(url, {code});
    }
} 