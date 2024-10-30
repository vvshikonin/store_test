import { defaultAPIInstance } from "./axios_instances";

export const permissionAPI = {
    index() {
        const url = '/api/v1/permissions';
        return defaultAPIInstance.get(url);
    },

}