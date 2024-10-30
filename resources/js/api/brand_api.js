import { defaultAPIInstance } from "./axios_instances";

export const brandsAPI = {
    async index(params) {
        const url = '/api/v1/brands';
        return defaultAPIInstance.get(url, {params});
    },
    async update(brand) {
        const url = '/api/v1/brands/' + brand.id;
        return defaultAPIInstance.patch(url, brand);
    },
    async destroy(brand) {
        const url = '/api/v1/brands/' + brand.id;
        return defaultAPIInstance.delete(url, brand);
    },
    async store(name) {
        const url = '/api/v1/brands';
        return defaultAPIInstance.post(url, name);
    },
    async export(params) {
        const url = '/api/v1/brands/export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    }
} 