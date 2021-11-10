import { createStore } from "vuex";
import actions from './actions';
import mutations from "./mutations";
import getters from "./getters";

export const store = createStore({
    state() {
        return {
            // token: '',
            pagination: {
                currentPage: 0,
                currentFilter: {}
            },
            modal: '',
            version: '',
            tags: [],
            institutions: [],
            accounts: [],
            accountTypes: [],
            entries: [],
            fetched: {
                institutions: false,
                accounts: false,
                accountTypes: false,
                tags: false,
                version: false
            }
        }
    },
    actions,
    mutations,
    getters
});