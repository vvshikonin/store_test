import { defaultAPIInstance } from "./axios_instances";

export const employeeAPI = {
    async index() {
        const url = '/api/v1/employees';
        return defaultAPIInstance.get(url, {});
    },
    async update(employee) {
        const url = '/api/v1/employees/' + employee.id;
        return defaultAPIInstance.patch(url, employee);
    },
    async destroy(employee) {
        const url = '/api/v1/employees/' + employee.id;
        return defaultAPIInstance.delete(url, employee);
    },
    async store(employee) {
        const url = '/api/v1/employees';
        return defaultAPIInstance.post(url, employee);
    },
} 