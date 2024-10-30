import { defectAPI } from "../../api/defect_api";

export const defectModule = {
    state() {
        return {
            defects: [],
            selection:{
                filter:{
                    isShowFilter: true,
                    date: {start: null, end: null},
                    order_number: null,
                    product: null,
                    contractor_id: null,
                    price: {start: null, end: null},
                    amount: {start: null, end: null},
                    summ: {start: null, end: null},
                    sheduled_status: null,
                    comment: null,
                    refund_status: null,
                },
                sort:{ field: 'created_at', type: 'desc' },
                pagination: { limit: 25, page: 1, count: 1 }
            },
            settings:{
                isShowTableSettings: false,
                tableDefault: {
                    0: { field: 'created_at', text: 'Дата', visability: true },
                    1: { field: 'order_number', text: 'Номер заказа', visability: true },
                    2: { field: 'sku', text: 'Артикул', visability: true },
                    3: { field: 'product_name', text: 'Название', visability: true },
                    4: { field: 'contractor_name', text: 'Поставщик', visability: true },
                    5: { field: 'price', text: 'Цена', visability: true },
                    6: { field: 'amount', text: 'Кол-во', visability: true },
                    7: { field: 'sum', text: 'Сумма', visability: true },
                    8: { field: 'status', text: 'Статус', visability: true },
                    9: { field: 'comment', text: 'Комментарий', visability: true },
                    10: { field: 'money_refund', text: 'Возврат ДС', visability: true },
                },
                table: null,
            }
        }
    },
    getters: {
        getDefects: (state) => state.defects,
        getDefectsSelection: (state) => state.selection,
        getDefectsSettings: (state) => state.settings,
    },
    mutations: {
        setDefects(state, defects){
            state.defects = defects;
        },
        setDefectsFilterDefault(state){
            state.selection.filter.date.start =  null;
            state.selection.filter.date.end =  null;
            state.selection.filter.order_number =  null;
            state.selection.filter.product =  null;
            state.selection.filter.contractor_id =  null;
            state.selection.filter.price.start =  null;
            state.selection.filter.price.end =  null;
            state.selection.filter.amount.start =  null;
            state.selection.filter.amount.end =  null;
            state.selection.filter.summ.start =  null;
            state.selection.filter.summ.end =  null;
            state.selection.filter.sheduled_status =  null;
            state.selection.filter.comment =  null;
            state.selection.filter.refund_status =  null;
        }
    },
    actions: {
        async indexDefects({commit, state}){
            return defectAPI.index({selection: state.selection}).then((res) => {
                commit('setDefects', res.data.defects);
                state.selection.pagination.count = res.data.page_count;
            })
        },
        async updateDefects({}, {id, comment, status}){
            return defectAPI.update(id, comment, status);
        },
        setDefectsFilterDefault({commit}){
            commit('setDefectsFilterDefault');
        }
    }
}
