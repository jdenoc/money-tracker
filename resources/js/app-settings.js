import Vue from 'vue';

import { createPinia, PiniaVuePlugin } from 'pinia';
Vue.use(PiniaVuePlugin);
const pinia = createPinia();

import Snotify from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 10000}});    // 10 seconds

// import VTooltip from 'v-tooltip';
// Vue.use(VTooltip);

import eventHub from "./plugins/eventHub";
Vue.use(eventHub);

// components
import LoadingModal from './components/loading-modal.vue';
import NavBar from './components/nav-bar.vue';
import NotificationItem from './components/notification-item.vue';
import SettingsDisplay from "../js/components/settings/settings-display.vue";
import SettingsNav from "../js/components/settings/settings-nav.vue";
// stores
import {useAccountsStore} from "./stores/accounts";
import {useAccountTypesStore} from "./stores/accountTypes";
import {useInstitutionsStore} from "./stores/institutions";
import {useTagsStore} from "./stores/tags";
import {useVersionStore} from "./stores/version";

new Vue({
  el: "#app-settings",
  components: {
    LoadingModal,
    NavBar,
    Notification: NotificationItem,
    SettingsDisplay,
    SettingsNav,
  },
  pinia,
  computed:{ },
  methods: {
    displayNotification: function(notification){
      this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
    },
  },
  mounted: function(){
    useInstitutionsStore().fetch()
      .then(this.displayNotification.bind(this));

    useAccountsStore().fetch()
      .then(this.displayNotification.bind(this));

    useAccountTypesStore().fetch()
      .then(this.displayNotification.bind(this));

    useTagsStore().fetch()
      .then(this.displayNotification.bind(this));

    useVersionStore().fetch();
  }
});