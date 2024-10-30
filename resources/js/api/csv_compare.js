import { defaultAPIInstance } from "./axios_instances";

export const csvCompareAPI = {
    async uploadFile(file) {
        const url = '/api/v1/csv-compare/upload-file';
        return defaultAPIInstance.post(url, file);
    },

    async getData(upload_index) {
        const url = '/api/v1/csv-compare/get-data';
        return defaultAPIInstance.get(url, { params: { upload_index } });
    },

    async updateComment(id, comment) {
        const url = '/api/v1/csv-compare/update-comment';
        return defaultAPIInstance.post(url, { id, comment });
    },
    async removeAll() {
        const url = '/api/v1/csv-compare/remove-all';
        return defaultAPIInstance.post(url);
    },

    async getUploads() {
        const url = '/api/v1/csv-compare/get-uploads';
        return defaultAPIInstance.get(url);
    },

    async getActualUpload() {
        const url = '/api/v1/csv-compare/get-actual';
        return defaultAPIInstance.get(url);
    },

    async checkUpload(id) {
        const url = '/api/v1/csv-compare/check';
        return defaultAPIInstance.post(url, { id });
    }
}
