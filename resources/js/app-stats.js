// vue
import { createApp } from 'vue';

// components
import LoadingModal from './components/loading-modal';
import Navbar from './components/navbar';
import Notification from './components/notification';
import Stats from "./components/stats/stats";
import StatsNav from "./components/stats/stats-nav";

// objects
import {Accounts} from "./accounts";
import {AccountTypes} from "./account-types";
import {Tags} from "./tags";
import {Version} from './version';

const appStats = createApp({
    components:{
        LoadingModal,
        Navbar,
        Notification,
        Stats,
        StatsNav
    },
    methods: {
        displayNotification: function(notification){
            this.$eventBus.broadcast(this.$eventBus.EVENT_NOTIFICATION(), notification);
        },
    },
    mounted() {
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

// store
import { store } from './store';
appStats.use(store);

// eventbus/emitter
import eventBus from "./plugins/eventBus";
appStats.use(eventBus);

// notifications/toast
import Notifications from '@kyvg/vue3-notification'
appStats.use(Notifications);

// tooltip
import VTooltipPlugin from 'v-tooltip';
appStats.use(VTooltipPlugin);

appStats.mount('#app-stats');