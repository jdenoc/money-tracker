import Vue from 'vue'
import Vuex from 'vuex'
// import createPersistedState from 'vuex-persistedstate'

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,

    state: {
        // token: '',
        currentPage: 0,
        version: '',
        tags: [],
        institutions: [],
        accounts: [],
        accountTypes: [],
        entries: []
    },
    getters: {
        // isAuthenticated: function(state){
        //     var decodedToken = jwt.decode(state.token);
        //     return (state.token.length !== 0 && decodedToken.exp > Math.floor(new Date().getTime()/1000));
        // },
        // token: function(state){
        //     return state.token;
        // }

        /**
         * @returns {number}
         */
        currentPage: function(state){
            return state.currentPage;
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

        getStateOf: function(state, getters){
            return function(storeType){
                let storeTypes = getters.getAllStoreTypes;
                if(storeTypes.indexOf(storeType) >= 0){
                    return state[storeType]
                } else {
                    return [];
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
    },
    mutations: {
        // authenticate: function(state, payload){
        //     if(jwt.decode(payload)){
        //         state.token = payload;
        //     }
        // },
        // unauthenticate: function(state){
        //     state.token = '';
        // }
        currentPage: function(state, pageNumber){
            state.currentPage = (pageNumber < 0) ? 0 : pageNumber
        },
        setStateOf: function(state, payload){
            let storeTypes = this.getters.getAllStoreTypes;
            if(storeTypes.indexOf(payload.type) >= 0){
                state[payload.type] = payload.value;
            }
        }
    },
    actions: {
        // authenticate: function(context, payload){
        //     context.commit('authenticate', payload);
        // },
        // unauthenticate: function(context){
        //     context.commit('unauthenticate');
        // }
        currentPage: function(context, payload){
            context.commit('currentPage', payload);
        },
        setStateOf: function(context, payload){
            context.commit('setStateOf', payload);
        }
    },
    plugins: [
        // createPersistedState()  // allows us to store state locally
    ]
});