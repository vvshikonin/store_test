import { paymentMethodAPI } from "../../api/payment_method_api";

export const paymentMethodModule = {
  state() {
    return {
      paymentMethods: [],
    };
  },
  getters: {
    getPaymentMethods: (state) => state.paymentMethods,
  },
  mutations: {
    setPaymentMethods(state, paymentMethods) {
      state.paymentMethods = paymentMethods;
    },
  },
  actions: {
    async loadPaymentMethods({ commit }, withType3) {
      try {
        const response = await paymentMethodAPI.index(withType3);
        const paymentMethods = response.data.data;
        commit('setPaymentMethods', paymentMethods);
      } catch (error) {
        console.error('Failed to load payment methods:', error);
      }
    }
  },
};
