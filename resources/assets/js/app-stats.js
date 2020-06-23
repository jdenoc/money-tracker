import Vue from 'vue';
import Store from './store';

import Snotify from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 8000}});    // 8 seconds

import VTooltip from 'v-tooltip';
Vue.use(VTooltip);

import eventHub from "./plugins/eventHub";
Vue.use(eventHub);

import LoadingModal from './components/loading-modal';
import Navbar from './components/navbar';
import Notification from './components/notification';
import Stats from "./components/stats/stats";
import StatsNav from "./components/stats/stats-nav";

import {AccountTypes} from "./account-types";
import {Tags} from "./tags";
import {Version} from './version';
import {Accounts} from "./accounts";

new Vue({
    el: "#app",
    components: {
        LoadingModal,
        Navbar,
        Notification,
        Stats,
        StatsNav
    },
    store: Store,
    computed:{ },
    methods: {
        displayNotification: function(notification){
            this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
        },
    },
    mounted: function(){
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