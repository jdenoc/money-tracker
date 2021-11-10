export default {

//         // isAuthenticated: function(state){
//         //     var decodedToken = jwt.decode(state.token);
//         //     return (state.token.length !== 0 && decodedToken.exp > Math.floor(new Date().getTime()/1000));
//         // },
//         // token: function(state){
//         //     return state.token;
//         // }

    /**
     * @returns {string}
     */
    currentModal: function(state){
        return state.modal;
    },

        /**
         * @returns {number}
         */
    currentPage: function(state){
        return state.pagination.currentPage;
    },

    /**
     * @returns {state.pagination.currentFilter|{}}
     */
    currentFilter: function(state){
        return state.pagination.currentFilter;
    },

    /**
     * @returns {string}
     */
    STORE_TYPE_ACCOUNTS: function(){ return 'accounts' },
    /**
     * @returns {string}
     */
    STORE_TYPE_ACCOUNT_TYPES: function(){ return 'accountTypes' },
    /**
     * @returns {string}
     */
    STORE_TYPE_ENTRIES: function(){ return 'entries' },
    /**
     * @returns {string}
     */
    STORE_TYPE_INSTITUTIONS: function(){ return 'institutions' },
    /**
     * @returns {string}
     */
    STORE_TYPE_TAGS: function(){ return 'tags' },
    /**
     * @returns {string}
     */
    STORE_TYPE_VERSION: function(){ return 'version' },

    /**
     * @returns {string}
     */
    STORE_MODAL_ENTRY: function(){ return 'modal-entry'; },
    /**
     * @returns {string}
     */
    STORE_MODAL_TRANSFER: function(){ return 'modal-transfer'; },
    /**
     * @returns {string}
     */
    STORE_MODAL_FILTER: function(){ return 'modal-filter'; },
    /**
     * @returns {string}
     */
    STORE_MODAL_NONE: function(){ return ''; },

    getStateOf: function(state, getters){
        return function(storeType){
            let storeTypes = getters.getAllStoreTypes;
            if(storeTypes.indexOf(storeType) >= 0){
                return state[storeType];
            } else {
                return [];
            }
        }
    },

    getFetchedState: function(state, getters){
        return function(storeType){
            let storeTypes = getters.getAllStoreTypes;
            if(storeTypes.indexOf(storeType) >= 0 && storeType !== getters.STORE_TYPE_ENTRIES){
                return state.fetched[storeType];
            } else {
                return false;
            }
        }
    },

    getAllStoreTypes: function(state, getters){
        return [
            getters.STORE_TYPE_ACCOUNTS,
            getters.STORE_TYPE_ACCOUNT_TYPES,
            getters.STORE_TYPE_ENTRIES,
            getters.STORE_TYPE_INSTITUTIONS,
            getters.STORE_TYPE_TAGS,
            getters.STORE_TYPE_VERSION,
        ];
    },

    getAllStoreModals: function(state, getters){
        return [
            getters.STORE_MODAL_ENTRY,
            getters.STORE_MODAL_TRANSFER,
            getters.STORE_MODAL_FILTER,
            getters.STORE_MODAL_NONE
        ];
    }
}