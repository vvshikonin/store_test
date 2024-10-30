import { defaultAPIInstance } from "./axios_instances";

export const settingsAPI = {
    index() {
        const url = '/api/v1/settings';
        return defaultAPIInstance.get(url);
    }
}