// vue
import { createApp } from 'vue';

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
import {AccountTypes} from './account-types';
import {Institutions} from "./institutions";
import {Tags} from './tags';
import {Version} from "./version";

const appHome = createApp({
    components:{
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
    methods: {
        displayNotification: function(notification){
            this.$eventBus.broadcast(this.$eventBus.EVENT_NOTIFICATION(), notification);
        },
    },
    mounted() {
        this.$eventBus.broadcast(this.$eventBus.EVENT_LOADING_SHOW());

        let version = new Version();
        version.fetch();

        let accountTypes = new AccountTypes();
        accountTypes.fetch().then(this.displayNotification.bind(this));

        let institutions = new Institutions();
        institutions.fetch().then(this.displayNotification.bind(this));

        this.$eventBus.broadcast(this.$eventBus.EVENT_ACCOUNT_UPDATE());

        this.$eventBus.broadcast(this.$eventBus.EVENT_ENTRY_TABLE_UPDATE(), 0);

        let tags = new Tags();
        tags.fetch().then(this.displayNotification.bind(this));
    }
});

// store
import { store } from './store';
appHome.use(store);

// eventbus/emitter
import eventBus from "./plugins/eventBus";
appHome.use(eventBus);

// notifications/toast
import Notifications from '@kyvg/vue3-notification'
appHome.use(Notifications);

// tooltip
import VTooltipPlugin from 'v-tooltip';
appHome.use(VTooltipPlugin);

appHome.mount('#app-home');


// import VueHotkey from 'v-hotkey';
// Vue.use(VueHotkey);

// new Vue({
//     computed:{
//         keymap: function(){
//             return {
//                 // FIXME: hotkey already used for something else on windows
//                 'ctrl+esc': function(){ // close modal
//                     switch(Store.getters.currentModal){
//                         case Store.getters.STORE_MODAL_ENTRY:
//                             console.debug('entry:ctrl+esc');
//                             this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_MODAL_CLOSE);
//                             break;
//                         case Store.getters.STORE_MODAL_TRANSFER:
//                             console.debug('transfer:ctrl+esc');
//                             this.$eventHub.broadcast(this.$eventHub.EVENT_TRANSFER_MODAL_CLOSE);
//                             break;
//                         case Store.getters.STORE_MODAL_FILTER:
//                             console.debug('filter:ctrl+esc');
//                             this.$eventHub.broadcast(this.$eventHub.EVENT_FILTER_MODAL_CLOSE);
//                             break;
//                         default:
//                             console.debug("ctrl+esc");
//                     }
//                 }.bind(this)
//             };
//         },
//     }
// });