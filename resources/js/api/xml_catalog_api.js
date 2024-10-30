import {defaultAPIInstance} from "./axios_instances";

export const XMLCatalogAPI = {
    async index() {
        const url = '/api/v1/xml_catalogs';
        return defaultAPIInstance.get(url);
    },
    async manual_generate() {
        const url = '/api/v1/xml_catalogs/manual_generate';
        return defaultAPIInstance.post(url);
    },
    async store(template) {
        const url = '/api/v1/xml_catalogs/store';
        return defaultAPIInstance.post(url, template);
    },
    async update(template) {
        const url = '/api/v1/xml_catalogs/update/' + template.id;
        return defaultAPIInstance.post(url, template);
    },
    async destroy(template_id) {
        const url = '/api/v1/xml_catalogs/destroy/' + template_id;
        return defaultAPIInstance.delete(url);
    }
} 