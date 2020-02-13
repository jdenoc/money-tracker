import Vue from 'vue';
import Store from './store';

import Snotify from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 8000}});    // 8 seconds

import VueHotkey from 'v-hotkey';
Vue.use(VueHotkey);

import VTooltip from 'v-tooltip';
Vue.use(VTooltip);

import EntryModal from './components/entry-modal';
import EntriesTable from './components/entries-table';
import EntriesTableEntryRow from './components/entries-table-entry-row';
import FilterModal from "./components/filter-modal";
import InstitutionsPanel from './components/institutions-panel';
import LoadingModal from './components/loading-modal';
import Navbar from './components/navbar';
import Notification from './components/notification';
import Stats from "./components/stats/stats";
import StatsNav from "./components/stats/stats-nav";
import TransferModal from './components/transfer-modal';

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
        EVENT_ACCOUNT_UPDATE: function(){ return 'update-accounts'; },
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
        EVENT_ENTRY_MODAL_CLOSE: function(){ return "close-entry-modal"; },
        /**
         * @returns {string}
         */
        EVENT_ENTRY_MODAL_UPDATE_DATA: function(){ return "update-data-in-entry-modal"; },
        /**
         * @returns {string}
         */
        EVENT_FILTER_MODAL_OPEN: function(){ return "open-filter-modal"; },
        /**
         * @returns {string}
         */
        EVENT_FILTER_MODAL_CLOSE: function(){ return "close-filter-modal"; },
        /**
         * @returns {string}
         */
        EVENT_TRANSFER_MODAL_OPEN: function(){ return "open-transfer-model"; },
        /**
         * @returns {string}
         */
        EVENT_TRANSFER_MODAL_CLOSE: function(){ return "close-transfer-model"; },
        /**
         * @returns {string}
         */
        EVENT_STATS_TRENDING: function(){ return 'stats-display-trending-chart'},
        /**
         * @returns {string}
         */
        EVENT_STATS_TAGS: function(){ return 'stats-display-tags-chart' }
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
        EntriesTableEntryRow,
        FilterModal,
        InstitutionsPanel,
        LoadingModal,
        Navbar,
        Notification,
        Stats,
        StatsNav,
        TransferModal
    },
    store: Store,
    computed:{
        keymap: function(){
            return {
                // FIXME: hotkey already used for something else on windows
                'ctrl+esc': function(){ // close modal
                    switch(Store.getters.currentModal){
                        case Store.getters.STORE_MODAL_ENTRY:
                            console.debug('entry:ctrl+esc');
                            this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_MODAL_CLOSE);
                            break;
                        case Store.getters.STORE_MODAL_TRANSFER:
                            console.debug('transfer:ctrl+esc');
                            this.$eventHub.broadcast(this.$eventHub.EVENT_TRANSFER_MODAL_CLOSE);
                            break;
                        case Store.getters.STORE_MODAL_FILTER:
                            console.debug('filter:ctrl+esc');
                            this.$eventHub.broadcast(this.$eventHub.EVENT_FILTER_MODAL_CLOSE);
                            break;
                        default:
                            console.debug("ctrl+esc");
                    }
                }.bind(this)
            };
        },
    },
    methods: {
        displayNotification: function(notification){
            this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
        },
    },
    mounted: function(){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        this.$eventHub.broadcast(this.$eventHub.EVENT_ACCOUNT_UPDATE);

        let accountTypes = new AccountTypes();
        accountTypes.fetch().then(this.displayNotification.bind(this));

        this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, 0);

        let institutions = new Institutions();
        institutions.fetch().then(this.displayNotification.bind(this));

        let tags = new Tags();
        tags.fetch().then(this.displayNotification.bind(this));

        let version = new Version();
        version.fetch();
    }
});