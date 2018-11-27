import Vue from 'vue';
import Store from './store';

import Snotify, { SnotifyStyle } from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 5000}});

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
        EVENT_NOTIFICATION: function(){ return "notification"; },
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

        // TODO: EVENT_UPDATE_ACCOUNTS: update accounts in institutions panel when there is an entry update
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
    methods: {
        displayNotification: function(notification){
            if(!_.isEmpty(notification)){
                switch(notification.type){
                    case SnotifyStyle.error:
                        this.$snotify.error(notification.message);
                        break;
                    case SnotifyStyle.info:
                    default:
                        this.$snotify.info(notification.message);
                        break;
                    case SnotifyStyle.success:
                        this.$snotify.success(notification.message);
                        break;
                    case SnotifyStyle.warning:
                        this.$snotify.warning(notification.message);
                        break;
                }
            }
        }
    },
    created: function(){
        this.$eventHub.listen(this.$eventHub.EVENT_NOTIFICATION, this.displayNotification);
    },
    mounted: function(){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        let accounts = new Accounts();
        accounts.fetch().then(this.displayNotification.bind(this));

        let accountTypes = new AccountTypes();
        accountTypes.fetch().then(this.displayNotification.bind(this));

        this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE);

        let institutions = new Institutions();
        institutions.fetch().then(this.displayNotification.bind(this));

        let tags = new Tags();
        tags.fetch().then(this.displayNotification.bind(this));

        let version = new Version();
        version.fetch();
    }
});