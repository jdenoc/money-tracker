import Vue from 'vue';

import { createPinia, PiniaVuePlugin } from 'pinia';
Vue.use(PiniaVuePlugin);
const pinia = createPinia();

import Snotify from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 8000}});    // 8 seconds

import VueHotkey from 'v-hotkey';
Vue.use(VueHotkey);

import VTooltip from 'v-tooltip';
Vue.use(VTooltip);

import eventHub from "./plugins/eventHub";
Vue.use(eventHub);

// components
import EntryModal from './components/home/entry-modal.vue';
import EntriesTable from './components/home/entries-table.vue';
import EntriesTableEntryRow from './components/home/entries-table-entry-row.vue';
import FilterModal from "./components/home/filter-modal.vue";
import InstitutionsPanel from './components/home/institutions-panel.vue';
import LoadingModal from './components/loading-modal.vue';
import NavBar from './components/nav-bar.vue';
import NotificationItem from './components/notification-item.vue';
import TransferModal from './components/home/transfer-modal.vue';
// stores
import {useAccountTypesStore} from "./stores/accountTypes";
import {useInstitutionsStore} from "./stores/institutions";
import {useModalStore} from "./stores/modal";
import {useTagsStore} from "./stores/tags";
import {useVersionStore} from "./stores/version";

new Vue({
  el: "#app-home",
  components: {
    EntryModal,
    EntriesTable,
    EntriesTableEntryRow,
    FilterModal,
    InstitutionsPanel,
    LoadingModal,
    NavBar,
    Notification: NotificationItem,
    TransferModal
  },
  pinia,
  computed:{
    searchHotkey: function(){
      return this.detectOs() === 'Mac OS' ? 'command+k' : 'ctrl+k';
    },

    keymap: function(){
      return {
        [this.searchHotkey]: function(){  // open filter-modal
          this.$eventHub.broadcast(this.$eventHub.EVENT_FILTER_MODAL_OPEN);
        }.bind(this),
        'esc': function(){ // close modal
          switch (useModalStore().activeModal){
            case useModalStore().MODAL_ENTRY:
              this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_MODAL_CLOSE);
              break;
            case useModalStore().MODAL_TRANSFER:
              this.$eventHub.broadcast(this.$eventHub.EVENT_TRANSFER_MODAL_CLOSE);
              break;
            case useModalStore().MODAL_FILTER:
              this.$eventHub.broadcast(this.$eventHub.EVENT_FILTER_MODAL_CLOSE);
              break;
            default:
              // do nothing
          }
        }.bind(this)
      };
    },
  },
  methods: {
    detectOs: function(){
      const platform = navigator.platform;
      if (platform.indexOf('Win') !== -1) return 'Windows';
      if (platform.indexOf('Mac') !== -1) return 'Mac OS';
      if (platform.indexOf('Linux') !== -1) return 'Linux';
      return 'Unknown';
    },
    displayNotification: function(notification){
      this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
    },
  },
  mounted: function(){
    this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

    this.$eventHub.broadcast(this.$eventHub.EVENT_ACCOUNT_UPDATE);

    useVersionStore().fetch();

    useAccountTypesStore().fetch()
      .then(this.displayNotification.bind(this));

    this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, 0);

    useInstitutionsStore().fetch()
      .then(this.displayNotification.bind(this));

    useTagsStore().fetch()
      .then(this.displayNotification.bind(this));
  }
});