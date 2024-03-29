import Vue from 'vue';
import Store from './store';

import Snotify from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 8000}});    // 8 seconds

import VueHotkey from 'v-hotkey';
Vue.use(VueHotkey);

import VTooltip from 'v-tooltip';
Vue.use(VTooltip);

import eventHub from "./plugins/eventHub";
Vue.use(eventHub);

// components
import EntryModal from './components/entry-modal';
import EntriesTable from './components/entries-table';
import EntriesTableEntryRow from './components/entries-table-entry-row';
import FilterModal from "./components/filter-modal";
import InstitutionsPanel from './components/institutions-panel';
import LoadingModal from './components/loading-modal';
import Navbar from './components/navbar';
import Notification from './components/notification';
import TransferModal from './components/transfer-modal';
// objects
import { AccountTypes } from './account-types';
import { Institutions } from './institutions';
import { Tags } from './tags';
import { Version } from './version';

new Vue({
    el: "#app-home",
    components: {
        EntryModal,
        EntriesTable,
        EntriesTableEntryRow,
        FilterModal,
        InstitutionsPanel,
        LoadingModal,
        Navbar,
        Notification,
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

        let version = new Version();
        version.fetch();

        let accountTypes = new AccountTypes();
        accountTypes.fetch().then(this.displayNotification.bind(this));

        this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, 0);

        let institutions = new Institutions();
        institutions.fetch().then(this.displayNotification.bind(this));

        let tags = new Tags();
        tags.fetch().then(this.displayNotification.bind(this));
    }
});