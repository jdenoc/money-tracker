export default {
//         // authenticate: function(context, payload){
//         //     context.commit('authenticate', payload);
//         // },
//         // unauthenticate: function(context){
//         //     context.commit('unauthenticate');
//         // }

    currentModal: function(context, payload){
        context.commit('currentModal', {
            modal: payload,
            acceptableModals: context.getters.getAllStoreModals,
            fallbackModal: context.getters.STORE_MODAL_NONE
        });
    },

    currentPage: function(context, payload){
        context.commit('currentPage', payload);
    },

    currentFilter: function(context, payload){
        context.commit('currentFilter', payload);
    },

    setStateOf: function(context, payload){
        context.commit('setStateOf', payload);
    },

    setFetchedState: function(context, payload){
        context.commit('setFetchedState', payload);
    }
}