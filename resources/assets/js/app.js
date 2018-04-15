import Vue from 'vue';
import Store from './store'
import InstitutionsPane from './components/institutions-pane';

import { Accounts } from './accounts';
import { AccountTypes } from "./account-types";
import { Institutions } from './institutions';
import { Tags } from './tags';
import { Version } from './version';

new Vue({
    el: "#app",
    components: {
        InstitutionsPane
    },
    store: Store,
    mounted: function(){
        let accounts = new Accounts();
        accounts.fetch();

        let accountTypes = new AccountTypes();
        accountTypes.fetch();

        let tags = new Tags();
        tags.fetch();

        let institutions = new Institutions();
        institutions.fetch();

        let version = new Version();
        version.fetch();
    }
});
