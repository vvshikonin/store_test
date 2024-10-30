import { productRefundAPI } from "../../api/product_refund_api";

export const productRefundModule = {
    state() {
        return {
            productRefund: [],
            selection:{
                filter:{
                    isFilterEmpty: true,
                    isShowFilter: true,
                    product: null,
                    amount:{ start: null, end: null },
                    price:{ start: null, end: null },
                    contractor_id: null,
                    refund_type: null,
                    order_number: null,
                    product_location: null,
                    comment: null,
                    created_at:{ start: null, end: null },
                    completed_at:{ start: null, end: null },
                },
                sort:{ field: 'created_at', type: 'desc' },
                pagination: { limit: 25, page: 1, count: 1 }
            },
            settings:{
                isShowTableSettings: false,
                tableDefault: {
                    2: { field: 'sku', text: 'Артикул', visability: true },
                    3: { field: 'product_name', text: 'Название', visability: true },
                    4: { field: 'amount', text: 'Кол-во', visability: true },
                    5: { field: 'price', text: 'Цена', visability: true },
                    6: { field: 'contractor_name', text: 'Поставщик', visability: true },
                    7: { field: 'type', text: 'Тип возврата', visability: true },
                    8: { field: 'product_location', text: 'Где товар', visability: true },
                    9: { field: 'order_number', text: 'Номер заказа', visability: true },
                    10: { field: 'comment', text: 'Комментарий', visability: true },
                    11: { field: 'created_at', text: 'Создан', visability: true },
                    12: { field: 'completed_at', text: 'Завершен', visability: true },
                },
                table: null,
            }
        }
    },
    getters: {
        getProductRefund: (state) => state.productRefund,
        getProductRefundSelection: (state) => state.selection,
        getProductRefundSettings: (state) => state.settings,
    },
    mutations: {
        setProductRefund(state, productRefund){
            state.productRefund = productRefund;
        },
        setFilterDefault(state){
            state.selection.filter.product = null;
            state.selection.filter.amount = { start: null, end: null };
            state.selection.filter.price = { start: null, end: null };
            state.selection.filter.contractor_id =  null;
            state.selection.filter.refund_type = null;
            state.selection.filter.order_number = null;
            state.selection.filter.comment = null;
            state.selection.filter.date = { start: null, end: null };
        }
    },
    actions: {
        async indexProductRefund({commit, state}){
            return productRefundAPI.index({selection: state.selection}).then((res) => {
                commit('setProductRefund', res.data.product_refunds);
                state.selection.pagination.count = res.data.page_count;
            })
        },
        async updateProductRefund({}, {id, comment}){
            return productRefundAPI.update(id, comment);
        },
        setFilterDefaultProductRefund({commit}){
            commit('setFilterDefault');
        }
    }
}
