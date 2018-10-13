import Vue from 'vue';
import Store from './store';

import EntryModal from './components/entry-modal';
import EntriesTable from './components/entries-table';
import InstitutionsPanel from './components/institutions-panel';
import Navbar from './components/navbar';

import { Accounts } from './accounts';
import { AccountTypes } from './account-types';
import { Entries } from './entries';
import { Institutions } from './institutions';
import { Tags } from './tags';
import { Version } from './version';

Vue.prototype.$eventHub = new Vue({
    computed: {
        /**
         * @returns {string}
         */
        EVENT_OPEN_ENTRY_MODAL: function(){ return "open-entry-modal";},
        /**
         * @returns {string}
         */
        EVENT_UPDATE_ENTRY_MODAL_DATA: function(){ return "update-data-in-entry-modal"; }
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
        Navbar
    },
    store: Store,
    mounted: function(){
        let accounts = new Accounts();
        accounts.fetch();

        let accountTypes = new AccountTypes();
        accountTypes.fetch();

        let entries = new Entries();
        entries.fetch();

        let institutions = new Institutions();
        institutions.fetch();

        let tags = new Tags();
        tags.fetch();

        let version = new Version();
        version.fetch();
    }
});