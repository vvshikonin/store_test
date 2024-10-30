import { defaultAPIInstance } from "./axios_instances";

export const rolesAPI = {
    index(params) {
        const url = '/api/v1/roles';
        return defaultAPIInstance.get(url, { params });
    },
    show(role_id) {
        const url = '/api/v1/roles/' + role_id;
        return defaultAPIInstance.get(url);
    },
    store(role) {
        const url = '/api/v1/roles';
        return defaultAPIInstance.post(url, role);
    },
    update(role_id, data) {
        const url = '/api/v1/roles/' + role_id;
        return defaultAPIInstance.put(url, data);
    },
    destroy(role_id) {
        const url = '/api/v1/roles/' + role_id;
        return defaultAPIInstance.delete(url);
    }
}
