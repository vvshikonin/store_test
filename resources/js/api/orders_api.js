import { defaultAPIInstance } from "./axios_instances";

export const ordersAPI = {
    index() {
        const url = '/api/orders';
        return defaultAPIInstance.post(url);
    },
    store(data){
        const url = '/api/orders/store';
        return defaultAPIInstance.post(url, data);
    },
    show(id){
        const url = '/api/orders/show/' + id; 
        return defaultAPIInstance.post(url);
    }
} 