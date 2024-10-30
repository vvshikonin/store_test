import { brandsAPI } from "../../api/brand_api";

export const brandModule = {
    state() {
        return {
            brands: [],
            meta: {},
        }
    },

    getters: {
        getBrands: (state) => state.brands,
        getBrandsMeta: (state) => state.meta,
    },

    mutations: {
        setBrands(state, brands) {
            state.brands = brands.data.data;
            state.meta = brands.data.meta;
        },
    },

    actions: {
        async loadBrands({ commit }, params) {
            return brandsAPI.index(params).then((res) => {
                commit('setBrands', res);
            });
        },
    }
}
