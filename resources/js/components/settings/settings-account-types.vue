<template>
  <section id="settings-account-types" class="max-w-lg">
    <h3 class="text-2xl mb-5 scroll-mt-16">Account Types</h3>
    <form class="grid grid-cols-6 gap-2">
      <!-- name -->
      <label for="settings-account-type-name" class="font-medium justify-self-end py-2 col-span-2">Name:</label>
      <input id="settings-account-type-name" name="name" type="text" class="rounded text-gray-700 col-span-4" v-model="form.name" autocomplete="off" v-bind:readonly="!form.active" />

      <!-- type -->
      <label for="settings-account-type-type" class="font-medium justify-self-end py-2 col-span-2">Type:</label>
      <div class="relative text-gray-700 col-span-4">
        <span class="loading absolute inset-y-3 left-2" v-show="!accountTypeTypesStore.isSet">
          <svg class="animate-spin mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </span>

        <select id="settings-account-type-type" class="rounded w-full" v-model="form.type" v-bind:disabled="!form.active">
          <option value="" selected></option>
          <option
              v-for="type in accountTypeTypesStore.list"
              v-bind:key="type"
              v-bind:value="type"
              v-text="type"
          ></option>
        </select>
      </div>

      <!-- last_digits -->
      <label for="settings-account-type-last-digits" class="font-medium justify-self-end py-2 col-span-2">Last Digits:</label>
      <input id="settings-account-type-last-digits" name="last_digits" type="text" class="rounded text-gray-700 col-span-4" maxlength="4" autocomplete="off"
             v-model="form.lastDigits"
             v-on:change="lastDigitsToNumber"
             v-bind:readonly="!form.active"
      />

      <!-- account -->
      <label for="settings-account-type-account" class="font-medium justify-self-end py-2 col-span-2">Account:</label>
      <div class="relative text-gray-700 col-span-4">
        <span class="loading absolute inset-y-3 left-2" v-show="!accountsStore.isSet">
          <svg class="animate-spin mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </span>

        <select id="settings-account-type-account" class="rounded w-full" v-model="form.accountId" v-bind:disabled="!form.active">
          <option value="" selected></option>
          <option
              v-for="account in accountsStore.list"
              v-bind:key="account.id"
              v-bind:value="account.id"
              v-text="account.name"
              v-show="account.active"
          ></option>
        </select>
      </div>

      <!-- Active State -->
      <label for="settings-account-type-active" class="font-medium justify-self-end py-2 col-span-2">Active State:</label>
      <div class="col-span-4">
        <toggle-button v-bind:toggle-state.sync="form.active"
                       v-bind="toggleButtonProperties"
                       toggle-id="settings-account-type-active"
                       v-on:click.native="resetFormAfterActiveStateToggle(form.id)"
        ></toggle-button>
      </div>

      <div class="font-medium" v-show="isDataInForm">Created:</div>
      <div class="col-span-5 italic text-sm self-center leading-none justify-self-end" v-show="isDataInForm" v-text="makeDateReadable(form.createStamp)"></div>

      <div class="font-medium" v-show="isDataInForm">Modified:</div>
      <div class="col-span-5 italic text-sm self-center leading-none justify-self-end" v-show="isDataInForm" v-text="makeDateReadable(form.modifiedStamp)"></div>

      <div class="font-medium" v-show="isDataInForm">Disabled:</div>
      <div class="col-span-5 italic text-sm self-center leading-none justify-self-end" v-show="isDataInForm" v-text="makeDateReadable(form.disabledStamp)"></div>

      <button type="button" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mx-1 mt-6 bg-gray-50 hover:bg-white col-span-3" v-on:click="setFormDefaults()">Clear</button>
      <button type="button" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 ml-1 mt-6 text-white bg-green-500 opacity-90 hover:opacity-100 col-span-3 disabled:opacity-50 disabled:cursor-not-allowed"
              v-on:click="save"
              v-bind:disabled="!canSave"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
        Save
      </button>
    </form>

    <hr class="my-6"/>

    <spinner v-if="!accountTypeTypesStore.isSet" id="loading-settings-account-types"></spinner>

    <ul class="mt-4 mr-8 mb-2 ml-2 text-sm" v-else>
      <li
          class="list-none p-4 mb-2 border "
          v-for="accountType in accountTypesStore.list"
          v-bind:key="accountType.id"
          v-bind:id="'settings-account-type-'+accountType.id"
          v-bind:class="{
            'border-l-4': form.id===accountType.id,
            'text-blue-400 border-blue-400 hover:border-blue-500 is-active': accountType.active,
            'text-gray-500 border-gray-500 hover:border-gray-700 is-disabled': !accountType.active
          }"
      >
        <span
            class="cursor-pointer"
            v-text="accountType.name"
            v-on:click="retrieveUpToDateAccountTypeData(accountType.id)"
        ></span>
      </li>
    </ul>
  </section>
</template>

<script>
// utilities
import _ from "lodash";
// mixins
import {settingsMixin} from "../../mixins/settings-mixin";
// components
import Spinner from 'vue-spinner-component/src/Spinner.vue';
import ToggleButton from '../toggle-button';
// objects
import {AccountType} from "../../account-type";
// stores
import {useAccountsStore} from "../../stores/accounts";
import {useAccountTypesStore} from "../../stores/accountTypes";
import {useAccountTypeTypesStore} from "../../stores/accountTypeTypes";

export default {
  name: "settings-account-types",
  components: {Spinner, ToggleButton},
  mixins: [settingsMixin],
  data: function(){
    return { }
  },
  computed: {
    accountsStore(){
      return useAccountsStore();
    },
    accountTypeObject: function(){
      return new AccountType();
    },
    accountTypesStore(){
      return useAccountTypesStore();
    },
    accountTypeTypesStore(){
      return useAccountTypeTypesStore();
    },
    canSave: function(){
      if(!_.isNull(this.form.id)){
        let accountTypeData = this.accountTypesStore.find(this.form.id);
        accountTypeData = this.sanitiseData(accountTypeData);
        return !_.isEqual(accountTypeData, this.form);
      } else {
        return !_.isEmpty(this.form.name) &&
            !_.isEmpty(this.form.type) &&
            !_.isEmpty(this.form.lastDigits) &&
            _.isNumber(this.form.accountId) && this.form.accountId !== 0;
      }
    },
    defaultFormData: function(){
      return {
        id: null,
        name: '',
        type: '',
        lastDigits: '',
        accountId: '',
        active: true,
        createStamp: '',
        modifiedStamp: '',
        disabledStamp: '',
      };
    },
    toggleButtonProperties: function(){
      return _.cloneDeep(this.defaultToggleButtonProperties);
    }
  },
  methods: {
    afterSaveResetFormAndHideLoading(){
      this.setFormDefaults();
      this.accountTypesStore.fetch().finally(function(){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
      }.bind(this));
    },
    lastDigitsToNumber(){
      if(!_.isEmpty(this.form.lastDigits)){
        this.form.lastDigits = this.form.lastDigits.replace(/[^0-9]/g, '');
      } else {
        this.form.lastDigits = '';
      }
    },
    makeDateReadable(isoDateString){
      if(_.isNull(isoDateString)){
        return isoDateString;
      } else {
        return new Date(isoDateString).toString();
      }
    },
    sanitiseData(data){
      Object.keys(data).forEach(function(k){
        switch(k){
          case 'account_id':
          case 'last_digits':
          case 'create_stamp':
          case 'modified_stamp':
          case 'disabled_stamp':
            data[_.camelCase(k)] = data[k];
            delete data[k];
            break;
          default:
          // do nothing
        }
      });
      return data;
    },
    save: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

      let accountTypeData = {};
      Object.keys(this.form).forEach(function(formDatumKey){
        switch (formDatumKey){
          case 'id':
          case 'name':
          case 'type':
            accountTypeData[formDatumKey] = this.form[formDatumKey];
            break;
          case 'lastDigits':
          case 'accountId':
            accountTypeData[_.snakeCase(formDatumKey)] = this.form[formDatumKey];
            break;
          default:
            // do nothing...
            break;
        }
      }.bind(this));

      // this.accountTypeObject.setFetchedState = false
      if(this.form.active){
        let upsertAccountType = function(){
          this.accountTypeObject
            .save(accountTypeData)
            .then(this.afterSaveDisplayNotificationIfNeeded)
            .finally(this.afterSaveResetFormAndHideLoading);
        }.bind(this);

        let existingAccountTypeData = this.accountTypesStore.find(accountTypeData['id']);
        if(_.isEmpty(existingAccountTypeData) || existingAccountTypeData.active){
          upsertAccountType(accountTypeData);
        } else {
          this.accountTypeObject.enable(accountTypeData['id'])
            .then(this.afterSaveDisplayNotificationIfNeeded)
            .finally(function(){
              upsertAccountType(accountTypeData);
            });
        }
      } else {
        this.accountTypeObject.disable(accountTypeData['id'])
          .then(this.afterSaveDisplayNotificationIfNeeded)
          .finally(this.afterSaveResetFormAndHideLoading);
      }
    },
    resetFormAfterActiveStateToggle(accountTypeId){
      // this is called AFTER toggle-state has been updated
      let accountTypeData = {}
      if(_.isNull(accountTypeId)){
        accountTypeData = _.clone(this.defaultFormData)
      } else {
        accountTypeData = _.clone(this.accountTypesStore.find(accountTypeId))
      }
      accountTypeData.active = this.form.active;
      this.fillForm(accountTypeData);
    },
    retrieveUpToDateAccountTypeData: function(accountTypeId){
      if(_.isNumber(accountTypeId)){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        // let accountTypeData = this.accountTypesStore.find(accountTypeId);
        // if(this.accountTypesObject.isDataUpToDate(accountTypeData)){
        //   this.fillForm(accountTypeData);
        //   this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
        // } else {
        this.accountTypeObject.fetch(accountTypeId)
          .then(function(fetchResult){
            if(fetchResult.fetched){
              let freshlyFetchedAccountData = this.accountTypesStore.find(accountTypeId);
              this.fillForm(freshlyFetchedAccountData);
            } else {
              this.setFormDefaults();
            }

            if(!_.isEmpty(fetchResult.notification)){
              this.$eventHub.broadcast(
                this.$eventHub.EVENT_NOTIFICATION,
                {type: fetchResult.notification.type, message: fetchResult.notification.message}
              );
            }
          }.bind(this))
          .finally(function(){
            this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
          }.bind(this));
        // }
      } else {
        this.setFormDefaults();
      }
    },
  },
  mounted: function() {
    this.setFormDefaults();
    useAccountTypeTypesStore().fetch();
  }
}
</script>

<style lang="scss" scoped>
</style>