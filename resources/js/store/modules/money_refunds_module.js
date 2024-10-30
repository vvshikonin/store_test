import { moneyRefundAPI } from "../../api/money_refund_api";
export const moneyRefundModule = {
    state: {
        index: [],
        positions: [],
        selection:{
            filter:{
                isFilterEmpty: true,
                isShowFilter: true,
                number: null,
                contractor: null,
                sum:{ start: null, end: null },
                status: null,
                type: null
            },
            sort:{ field: 'id', type: 'desc' },
            pagination:{ limit: 25, page: 1, count: 1 },
        },
        settings:{
            isShowTableSettings: false,
            tableDefault: {
                0: { field: null, text: null, visability: true },
                1: { field: 'id', text: '№', visability: true },
                2: { field: 'number', text: 'Номер счёта/заказа', visability: true },
                3: { field: 'contractors_names', text: 'Поставщики', visability: true },
                4: { field: 'sum', text: 'Сумма', visability: true },
                5: { field: 'status', text: 'Статус возврата', visability: true },
            },
            table: null,
        }
    },
    getters: {
        getMoneyRefund: (state) => state.index,
        getMoneyRefundSelection: (state) => state.selection,
        getMoneyRefundSettings: (state) => state.settings,
        getMoneyRefundPositions: (state) => state.positions

    },
    mutations: {
        setIndex(state, index) {
            state.index = index;
        },
        setFilterDefault(state){
            state.selection.filter.number = null;
            state.selection.filter.contractor = null;
            state.selection.filter.sum.start = null;
            state.selection.filter.sum.end = null;
            state.selection.filter.type = null;
            state.selection.filter.status = null;
        }
    },
    actions: {
        async indexMoneyRefund({ commit, state }) {
            return moneyRefundAPI.index(state.selection).then((res) => {
                commit('setIndex', res.data.refund);
                state.selection.pagination.count = res.data.page_count;
            });
        },
        async showMoneyRefund({state}, id){
            return moneyRefundAPI.show(id).then((res) => {
                state.positions[id] = res.data.data.positions;
                state.positions[id].visability = false;
            })
        },
        async updateMoneyRefund({}, {id, refund}){
            return moneyRefundAPI.update(id, refund);
        },
        setFilterDefaultMoneyRefund({commit}){
            commit('setFilterDefault');
        },
        clearPositions({state}){
            state.positions = [];
        }
    },
}