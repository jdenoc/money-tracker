<template>
  <li v-bind:id="'institution-'+id" class="institution-panel-institution block py-1 max-h-80">
    <div class="institution-panel-institution-name flex items-center py-2 px-3 cursor-pointer hover:bg-gray-50" v-on:click="toggleAccordion">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 -ml-1" viewBox="0 0 20 20" fill="currentColor" v-show="!isOpen">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 -ml-1" viewBox="0 0 20 20" fill="currentColor" v-show="isOpen">
        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
      </svg>
      <span v-text="name"></span>
    </div>
    <transition name="institution-accounts-slide-down">
      <ul class="institution-panel-institution-accounts list-inside ml-5 border-l border-gray-300"
          v-show="isOpen"
      >
        <institutions-panel-institution-account
            v-for="account in activeAccountsInInstitution"
            v-bind:key="account.id"
            v-bind:id="account.id"
            v-bind:name="account.name"
            v-bind:accountCurrency="account.currency"
            v-bind:total="account.total"
        ></institutions-panel-institution-account>
      </ul>
    </transition>
  </li>
</template>

<script lang="js">
import _ from 'lodash';
import {Accounts} from '../../accounts';
import InstitutionsPanelInstitutionAccount from "./institutions-panel-institution-account";

export default {
  name: "institutions-panel-institution",
  components: {InstitutionsPanelInstitutionAccount},
  props: {
    id: {type: Number, required: true},
    name: {type: String, required: true},
  },
  data: function(){
    return {
      isOpen: false,
      accountsObject: new Accounts(),
    };
  },
  computed: {
    accountsInInstitution: function(){
      return this.accountsObject.retrieve.filter(function(account){
        return account.institution_id === this.id;
      }.bind(this));
    },
    activeAccountsInInstitution: function(){
      return _.sortBy(
        this.accountsInInstitution.filter(function(account){
          return !account.disabled;
        }), 'name'
      );
    }
  },
  methods:{
    toggleAccordion: function(){
      this.isOpen = !this.isOpen;
    },
  }
}
</script>

<style lang="scss" scoped>
.institution-accounts-slide-down-enter-active,
.institution-accounts-slide-down-leave-active{
  $transition-time: 100ms;

  -webkit-transition: all #{$transition-time} ease-in-out;
  -moz-transition: all #{$transition-time} ease-in-out;
  -o-transition: all #{$transition-time} ease-in-out;
  transition: all #{$transition-time} ease-in-out;
}

.institution-accounts-slide-down-enter-from,
.institution-accounts-slide-down-leave-to {
  transform: translateY(-20%);  // slide down
  opacity: 0;
}
</style>