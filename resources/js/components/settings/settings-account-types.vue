<template>
  <section>
<!--  | id             | int(10) unsigned -->
  <form>
    <div class="field is-horizontal">
      <!-- name -->
      <div class="field-label is-normal">
        <label class="label" for="settings-account-type-name">Name:</label>
      </div>
      <div class="field-body"><div class="field"><div class="control">
        <input id="settings-account-type-name" name="name" type="text" class="input" v-model="form.name" />
      </div></div></div>
    </div>

    <div class="field is-horizontal">
      <!-- type -->
      <div class="field-label is-normal">
        <label class="label" for="settings-account-type-type">Type:</label>
      </div>
      <div class="field-body"><div class="field"><div class="control">
        <div class="select" v-bind:class="{'is-loading': !areAccountTypeTypesAvailable}">
          <select id="settings-account-type-type" v-model="form.type">
            <option value="" selected></option>
            <option
              v-for="type in listAccountTypeTypes"
              v-bind:key="type"
              v-bind:value="type"
              v-text="type"
            ></option>
          </select>
        </div>
      </div></div></div>
    </div>

    <div class="field is-horizontal">
      <!-- last_digits -->
      <div class="field-label is-normal">
        <label class="label" for="settings-account-type-last-digits">Last Digits:</label>
      </div>
      <div class="field-body"><div class="field"><div class="control">
        <input id="settings-account-type-last-digits" name="name" type="text" class="input" v-model="form.lastDigits" maxlength="4" />
      </div></div></div>
    </div>

    <div class="field is-horizontal">
      <!-- account -->
      <div class="field-label is-normal">
        <label class="label" for="settings-account-type-account">Account:</label>
      </div>
      <div class="field-body"><div class="field"><div class="control">
        <div class="select" v-bind:class="{'is-loading': !areAccountsAvailable}">
          <select id="settings-account-type-account" v-model="form.accountId">
            <option value="" selected></option>
            <option
              v-for="account in listAccounts"
              v-bind:key="account.id"
              v-bind:value="account.id"
              v-text="account.name"
              v-show="!account.disabled"
            ></option>
          </select>
        </div>
      </div></div></div>
    </div>

    <div class="field is-horizontal">
      <!-- Active State -->
      <div class="field-label is-normal"><label class="label" for="settings-account-type-disabled">Active State:</label></div>
      <div class="field-body"><div class="field"><div class="control">
        <toggle-button
          id="settings-account-type-disabled"
          v-model="form.disabled"
          v-bind:value="form.disabled"
          v-bind:labels="toggleButtonProperties.labels"
          v-bind:color="toggleButtonProperties.colors"
          v-bind:height="toggleButtonProperties.height"
          v-bind:width="toggleButtonProperties.width"
          v-bind:sync="true"
        />
      </div></div></div>
    </div>

    <div class="field is-horizontal" v-if="isDataInForm">
      <div class="field-label"><label class="label">Created:</label></div>
      <div class="field-body" v-text="makeDateReadable(form.createStamp)"></div>
    </div>

    <div class="field is-horizontal" v-if="isDataInForm">
      <div class="field-label"><label class="label">Modified:</label></div>
      <div class="field-body" v-text="makeDateReadable(form.modifiedStamp)"></div>
    </div>

    <div class="field is-horizontal" v-if="isDataInForm">
      <div class="field-label"><label class="label">Disabled:</label></div>
      <div class="field-body" v-text="makeDateReadable(form.disabledStamp)"></div>
    </div>

    <div class="field is-grouped is-grouped-centered">
      <div class="control">
        <button class="button is-primary" type="button" v-on:click="save()"><i class="fas fa-save"></i> Save</button>
      </div>
      <div class="control">
        <button class="button" type="button" v-on:click="setFormDefaults()">Clear</button>
      </div>
    </div>
  </form>

  <hr/>

  <ul class="block-list is-small is-info">
    <li
      v-for="accountType in listAccountTypes"
      v-bind:key="accountType.id"
      v-bind:id="'settings-account-type-'+accountType.id"
      v-bind:class="{'is-highlighted': form.id===accountType.id, 'is-outlined': !accountType.disabled, 'has-background-white-bis': accountType.disabled}"
    >
      <span
        v-text="accountType.name"
        v-bind:class="{'has-text-grey': accountType.disabled}"
        v-on:click="retrieveUpToDateAccountData(accountType.id)"
      ></span>
    </li>
  </ul>
  </section>
</template>

<script>
import _ from "lodash";
import {accountTypesObjectMixin} from "../../mixins/account-types-object-mixin";
import {accountsObjectMixin} from "../../mixins/accounts-object-mixin";
import {settingsMixin} from "../../mixins/settings-mixin";
import {ToggleButton} from 'vue-js-toggle-button';

export default {
  name: "settings-account-types",
  components: {ToggleButton},
  mixins: [settingsMixin, accountTypesObjectMixin, accountsObjectMixin],
  data: function(){
    return {

    }
  },
  computed: {
    formDefaultData: function(){
      return {
        id: null,
        name: '',
        type: '',
        lastDigits: '',
        accountId: 0,
        disabled: false,
        createStamp: '',
        modifiedStamp: '',
        disabledStamp: '',
      };
    },
    listAccounts: function(){
      return _.orderBy(this.rawAccountsData, ['disabled', 'name'], ['asc', 'asc']);
    },
    listAccountTypes: function(){
      return _.orderBy(this.rawAccountTypesData, ['disabled', 'name'], ['asc', 'asc']);
    },
    listAccountTypeTypes: function(){
      return _.orderBy(this.accountTypesObject.retrieveTypes);
    },
    areAccountTypeTypesAvailable: function(){
      return !_.isEmpty(this.listAccountTypeTypes);
    }
  },
  methods: {
    setFormDefaults: function(){
      this.fillForm(this.formDefaultData);
    },
    fillForm: function(accountTypeData){
      this.form = _.clone(accountTypeData);

      Object.keys(this.form).forEach(function(k){
        switch(k){
          case 'last_digits':
          case 'account_id':
          case 'create_stamp':
          case 'modified_stamp':
          case 'disabled_stamp':
            let camelCasedKey = _.camelCase(k);
            this.form[camelCasedKey] = this.form[k];
            delete this.form[k];
            break;
        }
      }.bind(this));
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
    },
    save: function(){
      // TODO: make API call to save (insert/update) account-type details
      console.warn("This feature is not yet ready");
    },
    makeDateReadable(isoDateString){
      return new Date(isoDateString).toString();
    },
    retrieveUpToDateAccountData: function(accountTypeId){
      if(_.isNumber(accountTypeId)){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        new Promise(function(resolve, reject){
          // TODO: make sure you're getting the most up-to-date data
          let accountTypeData = this.accountTypesObject.find(accountTypeId);
          // if(this.accountObject.isDataUpToDate(accountTypeData)){
            resolve(accountTypeData);
          // } else {
          //   reject(accountTypeId);
          // }
        }.bind(this))
          .then(this.fillForm.bind(this))
          .catch(function(accountTypeId){
            this.accountTypesObject.fetch(accountTypeId)
              .then(function(fetchResult){
                let freshlyFetchedAccountData = {};
                if(fetchResult.fetched){
                  freshlyFetchedAccountData = this.accountTypesObject.find(accountTypeId);
                }
                this.fillForm(freshlyFetchedAccountData);
                if(!_.isEmpty(fetchResult.notification)){
                  this.$eventHub.broadcast(
                    this.$eventHub.EVENT_NOTIFICATION,
                    {type: fetchResult.notification.type, message: fetchResult.notification.message}
                  );
                }
              }.bind(this));
          }.bind(this));
      } else {
        this.setFormDefaults();
      }
    }
  },
  mounted: function() {
    this.setFormDefaults();
    this.accountTypesObject.fetchTypes();
  }
}
</script>

<style lang="scss" scoped>
@import "../../../sass/settings";
</style>