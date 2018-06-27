import Vue from 'vue';
import Store from './store'
import EntriesTable from './components/entries-table';
import EntriesTableEntryRow from './components/entries-table-entry-row';
import Navbar from './components/navbar';
import InstitutionsPanel from './components/institutions-panel';

import { Accounts } from './accounts';
import { AccountTypes } from './account-types';
import { Entries } from './entries';
import { Institutions } from './institutions';
import { Tags } from './tags';
import { Version } from './version';

new Vue({
    el: "#app",
    components: {
        EntriesTable,
        EntriesTableEntryRow,
        InstitutionsPanel,
        Navbar
    },
    store: Store,
    mounted: function(){
        let accounts = new Accounts();
        accounts.fetch();

        let accountTypes = new AccountTypes();
        accountTypes.fetch();

        let entries = new Entries();
        entries.fetch();

        let tags = new Tags();
        tags.fetch();

        let institutions = new Institutions();
        institutions.fetch();

        let version = new Version();
        version.fetch();
    }
});
