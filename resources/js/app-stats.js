import Vue from 'vue';

import { createPinia, PiniaVuePlugin } from 'pinia';
Vue.use(PiniaVuePlugin);
const pinia = createPinia();

import Snotify from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 8000}});    // 8 seconds

import VTooltip from 'v-tooltip';
Vue.use(VTooltip);

import eventHub from "./plugins/eventHub";
Vue.use(eventHub);

// components
import LoadingModal from './components/loading-modal.vue';
import NavBar from './components/nav-bar.vue';
import NotificationItem from './components/notification-item.vue';
import StatsDisplay from "./components/stats/stats-display.vue";
import StatsNav from "./components/stats/stats-nav.vue";
// stores
import {useVersionStore} from "./stores/version";
import {useAccountsStore} from "./stores/accounts";
import {useAccountTypesStore} from "./stores/accountTypes";
import {useTagsStore} from "./stores/tags";

new Vue({
  el: "#app-stats",
  components: {
    LoadingModal,
    NavBar,
    Notification: NotificationItem,
    StatsDisplay,
    StatsNav
  },
  pinia,
  computed:{ },
  methods: {
    displayNotification: function(notification){
      this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
    },
  },
  mounted: function(){
    useAccountsStore().fetch()
      .then(this.displayNotification.bind(this));

    useAccountTypesStore().fetch()
      .then(this.displayNotification.bind(this));

    useTagsStore().fetch()
      .then(this.displayNotification.bind(this));

    useVersionStore().fetch();
  }
});