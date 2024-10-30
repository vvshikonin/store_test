import { defaultAPIInstance } from "./axios_instances";

export const paymentMethodAPI = {
    index(withType3 = false) {
        const url = '/api/v1/payment_methods';
        return defaultAPIInstance.get(url, { params: { withType3: withType3 } });
    },
    store(data) {
        const url = '/api/v1/payment_methods';
        return defaultAPIInstance.post(url, data);
    },
    update(id, paymentMethod) {
        const url = '/api/v1/payment_methods/' + id;
        return defaultAPIInstance.put(url, paymentMethod);
    },
    destroy(id) {
        const url = '/api/v1/payment_methods/' + id;
        return defaultAPIInstance.delete(url);
    }

} 