import { legalEntityAPI } from "../../api/legal_entity_api";

export const legalEntityModule = {
  state() {
    return {
      legalEntities: [],
      legalEntitiesMeta: [],
    }
  },

  getters: {
    getLegalEntities: (state) => state.legalEntities,
    getLegalEntitiesMeta: (state) => state.legalEntitiesMeta,
  },

  mutations: {
    setLegalEntities(state, legalEntities) {
      state.legalEntities = legalEntities;
    },
    setLegalEntitiesMeta(state, meta) {
      state.legalEntitiesMeta = meta;
    },
  },

  actions: {
    async loadLegalEntities({ commit }, params) {
      return legalEntityAPI.index(params).then((res) => {
        commit('setLegalEntities', res.data.data);
        commit('setLegalEntitiesMeta', res.data.meta);
      });
    },
  }
}
