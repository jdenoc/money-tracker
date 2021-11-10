export default {
//         // authenticate: function(state, payload){
//         //     if(jwt.decode(payload)){
//         //         state.token = payload;
//         //     }
//         // },
//         // unauthenticate: function(state){
//         //     state.token = '';
//         // }

    currentModal: function(state, {modal, acceptableModals, fallbackModal}){
        if(acceptableModals.indexOf(modal) !== -1){
            state.modal = modal;
        } else {
            state.modal = fallbackModal;
        }
    },

    currentPage: function(state, pageNumber){
        state.pagination.currentPage = (pageNumber < 0) ? 0 : pageNumber
    },

    currentFilter: function(state, filter){
        state.pagination.currentFilter = filter;
    },

    setStateOf: function(state, payload){
        let storeTypes = this.getters.getAllStoreTypes;
        if(storeTypes.indexOf(payload.type) >= 0){
            state[payload.type] = payload.value;
        }
    },

    setFetchedState: function(state, payload) {
        let storeTypes = this.getters.getAllStoreTypes;
        if (storeTypes.indexOf(payload.type) >= 0 && payload.type !== this.getters.STORE_TYPE_ENTRIES) {
            state.fetched[payload.type] = payload.value;
        }
    }
}