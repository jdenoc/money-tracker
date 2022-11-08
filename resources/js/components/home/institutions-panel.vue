<template>
  <!-- institutions - sidebar -->
  <nav id="institutions-panel" class="w-80 top-16 left-0 inset-y-0 fixed border-r">
    <div id="institutions-panel-header" class="block py-4 px-3 font-semibold text-xl bg-gray-100">Institutions</div>

    <ul>
      <li id="overview" class="block py-2 px-3 font-medium cursor-pointer"
          v-bind:class="{'text-white bg-blue-600 is-active': isOverviewFilterActive, 'text-blue-400 bg-white hover:bg-gray-50 hover:text-blue-500': !isOverviewFilterActive}"
          v-on:click="displayOverviewOfEntries"
      >
        <span>Overview</span>
        <!-- TODO: <span>(filtered)</span> should appear if "complex" filter has been engaged -->
      </li>

      <institutions-panel-institution
          v-for="institution in activeInstitutions"
          v-bind:key="institution.id"
          v-bind:id="institution.id"
          v-bind:name="institution.name"
      ></institutions-panel-institution>

      <li id="closed-accounts" class="block py-1 absolute bottom-0 inset-x-0 max-h-80" v-show="inactiveAccountsExist">
        <transition name="closed-institution-accounts-slide-up">
          <ul class="institution-panel-institution-accounts list-inside ml-5 border-l border-gray-300"
              v-show="isClosedAccountsAccordionOpen"
          >
            <institutions-panel-institution-account
                v-for="account in inactiveAccounts"
                v-bind:key="account.id"
                v-bind:id="account.id"
                v-bind:name="account.name"
                v-bind:canShowTooltip="false"
            ></institutions-panel-institution-account>
          </ul>
        </transition>

        <div class="flex items-center py-2 px-3 cursor-pointer hover:bg-gray-50" v-on:click="toggleClosedAccountsAccordion">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 -ml-1" viewBox="0 0 20 20" fill="currentColor" v-show="!isClosedAccountsAccordionOpen">
            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
          </svg>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 -ml-1" viewBox="0 0 20 20" fill="currentColor" v-show="isClosedAccountsAccordionOpen">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
          <span>Closed Accounts</span>
        </div>
      </li>

    </ul>
  </nav>
</template>

<script lang="js">
import {Institutions} from '../../institutions';
import InstitutionsPanelInstitution from "./institutions-panel-institution";
import InstitutionsPanelInstitutionAccount from './institutions-panel-institution-account';
import Store from '../../store';
import {accountsObjectMixin} from "../../mixins/accounts-object-mixin";

export default {
  name: "institutions-panel",
  components: {
    InstitutionsPanelInstitution,
    InstitutionsPanelInstitutionAccount
  },
  mixins: [accountsObjectMixin],
  data: function(){
    return {
      institutionsObject: new Institutions(),
      isClosedAccountsAccordionOpen: false,
    }
  },
  computed:{
    activeInstitutions: function(){
      return this.institutionsObject.retrieve.filter(function(institution){
        return institution.active;
      }).sort(function(a, b){
        if (a.name < b.name)
          return -1;
        if (a.name > b.name)
          return 1;
        return 0;
      });
    },
    inactiveAccounts: function(){
      return this.rawAccountsData.filter(function(account){
        return account.disabled;
      }).sort(function(a, b){
        if (a.name < b.name)
          return -1;
        if (a.name > b.name)
          return 1;
        return 0;
      });
    },
    inactiveAccountsExist: function(){
      return Object.keys(this.inactiveAccounts).length !== 0;
    },
    isOverviewFilterActive: function(){
      let currentFilter = Store.getters.currentFilter;
      return Object.keys(currentFilter).length === 0;
    },
  },
  methods:{
    displayOverviewOfEntries: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, {pageNumber: 0, filterParameters: {}});
    },
    toggleClosedAccountsAccordion: function(){
      this.isClosedAccountsAccordionOpen = !this.isClosedAccountsAccordionOpen;
    },
    updateAccountRecords: function(){
      this.accountsObject.setFetchedState = false;
      this.accountsObject.fetch().then(function(notification){
        this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
      }.bind(this));
    }
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_ACCOUNT_UPDATE, this.updateAccountRecords);
  }
}
</script>

<style lang="scss" scoped>
.panel {
  .closed-institution-accounts-slide-up-enter-active,
  .closed-institution-accounts-slide-up-leave-active {
    $transition-time: 100ms;

    -webkit-transition: all #{$transition-time} ease-in-out;
    -moz-transition: all #{$transition-time} ease-in-out;
    -o-transition: all #{$transition-time} ease-in-out;
    transition: all #{$transition-time} ease-in-out;
  }

  .closed-institution-accounts-slide-up-enter-from,
  .closed-institution-accounts-slide-up-leave-to {
    transform: translateY(20%);  // slide up
    opacity: 0;
  }
}
</style>