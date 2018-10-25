import Vue from 'vue';
import Store from './store';

import EntryModal from './components/entry-modal';
import EntriesTable from './components/entries-table';
import InstitutionsPanel from './components/institutions-panel';
import LoadingModal from './components/loading-modal';
import Navbar from './components/navbar';

import { Accounts } from './accounts';
import { AccountTypes } from './account-types';
import { Institutions } from './institutions';
import { Tags } from './tags';
import { Version } from './version';

Vue.prototype.$eventHub = new Vue({
    computed: {
        /**
         * @returns {string}
         */
        EVENT_LOADING_SHOW: function(){ return "loading-true"; },
        /**
         * @returns {string}
         */
        EVENT_LOADING_HIDE: function(){ return "loading-false"; },
        /**
         * @returns {string}
         */
        EVENT_ENTRY_TABLE_UPDATE: function(){ return "update-entry-table"; },
        /**
         * @returns {string}
         */
        EVENT_ENTRY_MODAL_OPEN: function(){ return "open-entry-modal";},
        /**
         * @returns {string}
         */
        EVENT_ENTRY_MODAL_UPDATE_DATA: function(){ return "update-data-in-entry-modal"; }
    },
    methods: {
        broadcast(event, data = null){
            this.$emit(event, data);
        },
        listen(event, callback){
            this.$on(event, callback);
        }
    }
});

new Vue({
    el: "#app",
    components: {
        EntryModal,
        EntriesTable,
        InstitutionsPanel,
        LoadingModal,
        Navbar
    },
    store: Store,
    mounted: function(){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        let accounts = new Accounts();
        accounts.fetch();

        let accountTypes = new AccountTypes();
        accountTypes.fetch();

        this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE);

        let institutions = new Institutions();
        institutions.fetch();

        let tags = new Tags();
        tags.fetch();

        let version = new Version();
        version.fetch();
    }
});