import { defaultAPIInstance } from "./axios_instances";

export const productAPI = {
    index(params) {
        const url = '/api/v1/products';
        return defaultAPIInstance.get(url, { params });
    },
    store({ product }) {
        const url = '/api/v1/products';
        return defaultAPIInstance.post(url, product);
    },
    show(product_id) {
        const url = '/api/v1/products/' + product_id;
        return defaultAPIInstance.get(url);
    },
    update({ product }) {
        const url = '/api/v1/products/' + product.id;
        return defaultAPIInstance.put(url, product);
    },
    checkSku({ sku }) {
        const url = '/api/v1/products/check_sku';
        return defaultAPIInstance.get(url, { params: { sku: sku } });
    },
    merge(a_product_id, b_product_id) {
        const url = '/api/v1/products/merge';

        a_product_id = Number(a_product_id);
        b_product_id = Number(b_product_id);

        const data = {
            a_product_id: a_product_id,
            b_product_id: b_product_id
        };
        return defaultAPIInstance.post(url, data);
    },

    async products_export(params) {
        const url = '/api/v1/products/products_export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    store_positions_export(params) {
        const url = '/api/v1/products/store_positions_export';
        const config = { params, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    transactions_export(product_id) {
        const url = '/api/v1/products/' + product_id + '/transactions_export';
        const config = { product_id, responseType: 'blob' };
        return defaultAPIInstance.get(url, config);
    },
    bulkStore(request) {
        const url = '/api/v1/products/bulk_store';
        return defaultAPIInstance.get(url, request);
    },
    bulkSearch(sku_array) {
        const url = '/api/v1/products/bulk_search';
        return defaultAPIInstance.post(url, { sku_array });
    },
    search(params) {
        const url = '/api/v1/products/search';
        return defaultAPIInstance.get(url, {params});
    },
    correct(product) {
        const url = '/api/v1/products/correct/' + product.id;
        return defaultAPIInstance.put(url, product);
    },
    getTransactions(product) {
        const url = '/api/v1/products/' + product.id + '/transactions';
        return defaultAPIInstance.get(url, product);
    },
    destroy(product_id) {
        const url = '/api/v1/products/' + product_id;
        return defaultAPIInstance.delete(url);
    }
}
