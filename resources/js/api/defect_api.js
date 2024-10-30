import { defaultAPIInstance } from "./axios_instances";

export const defectAPI = {
    async index(selection){
        const url = '/api/v1/defects';
        return defaultAPIInstance.get(url, {params: selection});
    },
    async show(id) {
        const url = '/api/v1/defects/' + id;
        return defaultAPIInstance.get(url);
    },
    async update({ defect }) {
        const url = '/api/v1/defects/' + defect.id;
        return defaultAPIInstance.put(url, defect);
    },
    // async defects_export(params) {
    //     const url = '/api/v1/defects/defects_export';
    //     const config = { params, responseType: 'blob' };
    //     return defaultAPIInstance.get(url, config);
    // },
    async defect_products_export(params) {
        const url = '/api/v1/defects/defect-products-export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    async loadFiles(data, id) {
        const url ='/api/v1/defects/defect-load-files/' + id;
        return defaultAPIInstance.post(url, data);
    },
    async deleteFile(link, id) {
        const url ='/api/v1/defects/defect-delete-file/' + id;
        return defaultAPIInstance.post(url, { link: link });
    }
}
