import { contractorAPI } from "../../api/contractor_api";

export const contractorModule = {
    state() {
        return {
            meta: [],
            contractors: [],
        }
    },

    getters: {
        getContractors: (state) => state.contractors,
        getContractorsMeta: (state) => state.meta,
    },

    mutations: {
        setContractors(state, [contractors]) {
            state.contractors = contractors;
        },
        setContractorsMeta(state, [meta]) {
            state.meta = meta;
        },
    },

    actions: {
        async loadContractorsData({ commit }, params) {
            return contractorAPI.index(params).then((res) => {
                commit('setContractors', [res.data.data]);
                commit('setContractorsMeta', [res.data.meta]);
            })
        },
    }
}