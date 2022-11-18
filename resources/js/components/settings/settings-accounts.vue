<template>
  <section id="settings-accounts" class="max-w-lg">
    <h3 class="text-2xl mb-5 scroll-mt-16">Accounts</h3>
    <form class="grid grid-cols-6 gap-2">
      <!-- name -->
      <label for="settings-account-name" class="font-medium justify-self-end py-2 col-span-2">Name:</label>
      <input id="settings-account-name" name="name" type="text" class="rounded text-gray-700 col-span-4" autocomplete="off" v-model="form.name" v-bind:readonly="!form.active" />

      <!-- institution -->
      <label for="settings-account-institution" class="font-medium justify-self-end py-2 col-span-2">Institution:</label>
      <div class="relative text-gray-700 col-span-4">
        <span class="loading absolute inset-y-3 left-2" v-show="!areInstitutionsAvailable">
          <svg class="animate-spin mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </span>

        <select id="settings-account-institution" class="rounded w-full" v-model="form.institutionId" v-bind:disabled="!form.active">
          <option value="" selected></option>
          <option
              v-for="institution in listInstitutions"
              v-bind:key="institution.id"
              v-bind:value="institution.id"
              v-text="institution.name"
              v-show="institution.active"
          ></option>
        </select>
      </div>

      <!-- currency -->
      <div class="font-medium justify-self-end py-2 col-span-2">Currency:</div>
      <div class="col-span-4 flex flex-wrap gap-y-1 gap-x-1.5">
        <label class="settings-account-currency rounded-md px-2 py-1.5 text-base border border-gray-300"
               v-for="currency in listCurrencies"
               v-bind:class="{
                  'bg-white hover:bg-gray-100 text-gray-700': form.currency !== currency.code,
                  'bg-blue-600 text-white opacity-90 hover:opacity-100 ': form.currency === currency.code,
                }"
               v-bind:key="currency.code"
        >
          <input type="radio" name="settings-account-currency" class="appearance-none hidden"
                 v-bind:id="'settings-account-currency-'+currency.label"
                 v-model="form.currency"
                 v-bind:value="currency.code"
                 v-bind:disabled="!form.active"
          />
          <span v-text="currency.code"></span>
        </label>
      </div>

      <!-- total -->
      <label for="settings-account-total" class="font-medium justify-self-end py-2 col-span-2">Total:</label>
      <div class="col-span-4 relative text-gray-700">
        <input id="settings-account-total" name="total" type="text" class="placeholder-gray-400 placeholder-opacity-80 rounded w-full pl-6" placeholder="0.00" autocomplete="off"
               v-model="form.total"
               v-on:change="decimaliseTotal"
               v-bind:readonly="!form.active"
        />
        <span class="absolute left-3 inset-y-2 mt-px text-gray-400 font-medium" v-html="currencyObject.getHtmlFromCode(form.currency)"></span>
      </div>

      <!-- Active State -->
      <label for="settings-account-active" class="font-medium justify-self-end py-2 col-span-2">Active State:</label>
      <div class="col-span-4">
        <toggle-button v-bind:toggle-state.sync="form.active"
                       v-bind="toggleButtonProperties"
                       toggle-id="settings-account-active"
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

    <!-- TODO: display account-types in a non-interactive way -->

    <hr class="my-6"/>

    <spinner v-if="!areAccountsAvailable" id="loading-settings-accounts"></spinner>

    <ul class="mt-4 mr-8 mb-2 ml-2 text-sm" v-else>
      <li
          class="list-none p-4 mb-2 border "
          v-for="account in listAccounts"
          v-bind:key="account.id"
          v-bind:id="'settings-account-'+account.id"
          v-bind:class="{
            'border-l-4': form.id===account.id,
            'text-blue-400 border-blue-400 hover:border-blue-500 is-active': account.active,
            'text-gray-500 border-gray-500 hover:border-gray-700 is-disabled': !account.active
          }"
      >
        <span
            class="cursor-pointer"
            v-text="account.name"
            v-on:click="retrieveUpToDateAccountData(account.id)"
        ></span>
      </li>
    </ul>
  </section>
</template>

<script>
// utilities
import _ from "lodash";
import {Account} from "../../account";
import {Currency} from "../../currency";
// mixins
import {accountsObjectMixin} from '../../mixins/accounts-object-mixin';
import {institutionsObjectMixin} from "../../mixins/institutions-object-mixin";
import {decimaliseInputMixin} from "../../mixins/decimalise-input-mixin";
import {settingsMixin} from "../../mixins/settings-mixin";
// components
import Spinner from 'vue-spinner-component/src/Spinner.vue';
import ToggleButton from "../toggle-button";

export default {
  name: "settings-accounts",
  components: {
    ToggleButton,
    Spinner
  },
  mixins: [accountsObjectMixin, decimaliseInputMixin, institutionsObjectMixin, settingsMixin],
  data: function(){
    return { }
  },
  computed: {
    accountObject: function(){
      return new Account();
    },
    canSave: function(){
      if(!_.isNull(this.form.id)){
        let accountData = this.accountObject.find(this.form.id);
        accountData = this.sanitiseData(accountData);
        return !_.isEqual(accountData, this.form);
      } else {
        return !_.isEmpty(this.form.name) &&
          _.isNumber(this.form.institutionId) &&
          !_.isEmpty(this.form.currency) &&
          !_.isEmpty(this.form.total);
      }
    },
    currencyObject: function(){
      return new Currency();
    },
    defaultFormData: function(){
      return {
        id: null,
        name: '',
        institutionId: "",
        active: true,
        total: '',
        currency: this.currencyObject.default.code,
        createStamp: '',
        modifiedStamp: '',
        disabledStamp: '',
        // accountTypes: [],
      };
    },
    listAccounts: function(){
      return _.orderBy(this.rawAccountsData, ['active', 'name'], ['desc', 'asc']);
    },
    listCurrencies: function(){
      return _.sortBy(this.currencyObject.list(), ['code']);
    },
    listInstitutions: function(){
      return _.sortBy(this.rawInstitutionsData, 'name');
    },
    toggleButtonProperties: function(){
      return _.cloneDeep(this.defaultToggleButtonProperties);
    }
  },
  methods: {
    afterSaveDisplayNotificationIfNeeded(notification){
      // show a notification if needed
      if(!_.isEmpty(notification)){
        this.$eventHub.broadcast(
          this.$eventHub.EVENT_NOTIFICATION,
          {type: notification.type, message: notification.message}
        );
      }
    },
    afterSaveResetFormAndHideLoading(){
      this.setFormDefaults();
      this.fetchAccounts().finally(function(){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
      }.bind(this));
    },
    decimaliseTotal: function(){
      if(!_.isEmpty(this.form.total)){
        this.form.total = this.decimaliseValue(this.form.total);
      }
    },
    makeDateReadable(isoDateString){
      if(_.isNull(isoDateString)){
        return isoDateString;
      } else {
        return new Date(isoDateString).toString();
      }
    },
    resetFormAfterActiveStateToggle(accountId){
      // this is called AFTER toggle-state has been updated
      let accountData = _.clone(this.accountObject.find(accountId));
      accountData.active = this.form.active;
      this.fillForm(accountData);
    },
    retrieveUpToDateAccountData: function(accountId = null){
      if(_.isNumber(accountId)){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        let accountData = this.accountObject.find(accountId);
        if(this.accountObject.isDataUpToDate(accountData)){
          this.fillForm(accountData);
          this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
        } else {
          this.accountObject
            .fetch(accountId)
            .then(function(fetchResult){
              if(fetchResult.fetched){
                let freshlyFetchedAccountData = this.accountObject.find(accountId);
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
        }
      } else {
        this.setFormDefaults();
      }
    },
    sanitiseData(data){
      Object.keys(data).forEach(function(k){
        switch(k){
          case 'institution_id':
          case 'create_stamp':
          case 'modified_stamp':
          case 'disabled_stamp': {
            let camelCasedKey = _.camelCase(k);
            data[camelCasedKey] = data[k];
            delete data[k];
            break;
          }
          default:
            // do nothing
        }
      }.bind(this));
      return data;
    },
    save: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

      let accountData = {};
      Object.keys(this.form).forEach(function(formDatumKey){
        switch (formDatumKey){
          case 'id':
          case 'name':
          case 'total':
          case 'currency':
            accountData[formDatumKey] = this.form[formDatumKey];
            break;
          case 'institutionId':
            accountData['institution_id'] = this.form[formDatumKey];
            break;
          default:
            // do nothing...
            break;
        }
      }.bind(this));

      this.accountObject.setFetchedState = false
      if(this.form.active){
        let updateAccount = function(accountData){
          this.accountObject.save(accountData)
            .then(this.afterSaveDisplayNotificationIfNeeded)
            .finally(this.afterSaveResetFormAndHideLoading);
        }.bind(this);

        let existingAccountData = this.accountObject.find(accountData['id']);
        if(existingAccountData.active){
          updateAccount(accountData);
        } else {
          this.accountObject.restore(accountData['id'])
            .then(this.afterSaveDisplayNotificationIfNeeded)
            .finally(function(){
              updateAccount(accountData)
            });
        }
      } else {
        this.accountObject.delete(accountData['id'])
          .then(this.afterSaveDisplayNotificationIfNeeded)
          .finally(this.afterSaveResetFormAndHideLoading);
      }
    },
  },
  mounted: function(){
    this.setFormDefaults();
  }
}
</script>

<style lang="scss" scoped>
</style>