import Vue from 'vue';
import Store from './store';

import Snotify from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 10000}});    // 10 seconds

// import VTooltip from 'v-tooltip';
// Vue.use(VTooltip);

import eventHub from "./plugins/eventHub";
Vue.use(eventHub);

// components
import LoadingModal from './components/loading-modal';
import NavBar from './components/nav-bar';
import NotificationItem from './components/notification-item';
import SettingsDisplay from "../js/components/settings/settings-display";
import SettingsNav from "../js/components/settings/settings-nav";
// objects
import {Accounts} from "./accounts";
import {AccountTypes} from "./account-types";
import {Institutions} from "./institutions";
import {Tags} from "./tags";
import {Version} from './version';

new Vue({
  el: "#app-settings",
  components: {
    LoadingModal,
    NavBar,
    Notification: NotificationItem,
    SettingsDisplay,
    SettingsNav,
  },
  store: Store,
  computed:{ },
  methods: {
    displayNotification: function(notification){
      this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
    },
  },
  mounted: function(){
    let institutions = new Institutions();
    institutions.fetch().then(this.displayNotification.bind(this));

    let accounts = new Accounts();
    accounts.fetch().then(this.displayNotification.bind(this));

    let accountTypes = new AccountTypes();
    accountTypes.fetch().then(this.displayNotification.bind(this));

    let tags = new Tags();
    tags.fetch().then(this.displayNotification.bind(this));

    let version = new Version();
    version.fetch();
  }
});