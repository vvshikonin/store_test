import { defaultAPIInstance } from "./axios_instances";

export const storePositionAPI = {
    async update(storePosition) {
        const url = '/api/v1/stocks/' + storePosition.id;
        return defaultAPIInstance.put(url, storePosition);
    },
}
