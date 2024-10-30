export const appSettingsModule = {
    state() {
        return {
            sidebarSettings: {
                mode: window.innerWidth < 768 ? false : (localStorage.getItem("sidebarMode") === 'true' ? true : false),
            }
        }
    },
    getters: {
        getSidebarMode: (state) => state.sidebarSettings.mode,
    },
    mutations:{
        toggleMode(state){
            state.sidebarSettings.mode = !state.sidebarSettings.mode;
            localStorage.setItem("sidebarMode", state.sidebarSettings.mode);
        }
    },
    actions:{
        toggleSidebarMode({commit}){
            commit('toggleMode');
        }
    }
}