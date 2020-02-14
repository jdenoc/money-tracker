import Vue from 'vue';
import Store from './store';

import Snotify from 'vue-snotify';
Vue.use(Snotify, {toast: {timeout: 8000}});    // 8 seconds

import eventHub from "./plugins/eventHub";
Vue.use(eventHub);

import LoadingModal from './components/loading-modal';
import Navbar from './components/navbar';
import Notification from './components/notification';
import Stats from "./components/stats/stats";
import StatsNav from "./components/stats/stats-nav";

import { Version } from './version';

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
    methods: { },
    mounted: function(){
        let version = new Version();
        version.fetch();
    }
});