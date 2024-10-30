import { productAPI } from "../../api/products_api";

export const productModule = {
    state: {
        products: []
    },
    getters: {
        getProducts(state) {
            return state.products;
        }
    },
    actions: {
        async indexProducts({ commit }, { params }) {
            return productAPI.index(params).then((res) => { 
                commit('setProducts', res.data.data) 
            });
        }
    },
    mutations: {
        setProducts(state, products) {
            state.products = products;
        }
    }
}