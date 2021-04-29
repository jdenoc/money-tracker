<template>
  <section id="settings-accounts" class="container">
    <h3 class="subtitle is-3 is-family-monospace"><i aria-hidden="true" class="fas fa-cogs"></i> Accounts</h3>
    <form>
      <div class="field is-horizontal">
        <!-- name -->
        <div class="field-label is-normal">
          <label class="label" for="settings-account-name">Name:</label>
        </div>
        <div class="field-body"><div class="field"><div class="control">
          <input id="settings-account-name" name="name" type="text" class="input" v-model="form.name" />
        </div></div></div>
      </div>

      <div class="field is-horizontal">
        <!-- institution -->
        <div class="field-label is-normal">
          <label class="label" for="settings-account-institution">Institution:</label>
        </div>
        <div class="field-body"><div class="field"><div class="control">
          <div class="select">
            <select id="settings-account-institution" v-model="form.institutionId" v-bind:class="{'is-loading': !areInstitutionsAvailable}">
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
        </div></div></div>
      </div>

      <div class="field is-horizontal">
        <!-- total -->
        <div class="field-label is-normal">
          <label class="label" for="settings-account-total">Total:</label>
        </div>
        <div class="field-body"><div class="field"><div class="control has-icons-left">
          <input id="settings-account-total" name="total" type="text" class="input" placeholder="0.00"
            v-model="form.total"
            v-on:change="decimaliseTotal"
          />
          <span class="icon is-small is-left">
            <i v-bind:class="currencyObject.getClassFromCode(form.currency)"></i>
          </span>
        </div></div></div>
      </div>

      <div class="field is-horizontal">
        <!-- currency -->
        <div class="field-label is-normal"><label class="label">Currency:</label></div>
        <div class="field-body"><div class="field is-grouped">
          <span v-for="currency in listCurrencies" class="settings-account-currency">
            <input class="is-checkradio is-info" type="radio" name="currency"
              v-bind:id="'settings-account-currency-'+currency.label"
              v-bind:value="currency.code"
              v-model="form.currency"
            />
            <label v-bind:for="'settings-account-currency-'+currency.label" v-text="currency.code">
              <i v-bind:class="currency.class"></i>
            </label>
          </span>
        </div></div>
      </div>

      <div class="field is-horizontal">
        <!-- Active State -->
        <div class="field-label is-normal"><label class="label" for="settings-account-disabled">Active State:</label></div>
        <div class="field-body"><div class="field"><div class="control">
          <toggle-button
              id="settings-account-disabled"
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

    <!-- TODO: display account-types in a non-interactive way -->

    <hr/>

    <spinner v-if="!areAccountsAvailable"></spinner>

    <ul class="block-list is-small is-info" v-else>
      <li
        v-for="account in listAccounts"
        v-bind:key="account.id"
        v-bind:id="'settings-account-'+account.id"
        v-bind:class="{'is-highlighted': form.id===account.id, 'is-outlined': !account.disabled, 'has-background-white-bis': account.disabled}"
      >
        <span
          v-text="account.name"
          v-bind:class="{'has-text-grey': account.disabled}"
          v-on:click="retrieveUpToDateAccountData(account.id)"
        ></span>
      </li>
    </ul>
  </section>
</template>

<script>
import {Account} from "../../account";
import {Currency} from "../../currency";
import _ from "lodash";
import {accountsObjectMixin} from '../../mixins/accounts-object-mixin';
import {institutionsObjectMixin} from "../../mixins/institutions-object-mixin";
import {decimaliseInputMixin} from "../../mixins/decimalise-input-mixin";
import {settingsMixin} from "../../mixins/settings-mixin";
import {ToggleButton} from 'vue-js-toggle-button';

import Spinner from 'vue-spinner-component/src/Spinner.vue';

export default {
  name: "settings-accounts",
  components: {
    ToggleButton,
    Spinner
  },
  mixins: [accountsObjectMixin, decimaliseInputMixin, settingsMixin],
  data: function(){
    return {
      accountObject: new Account(),
      currencyObject: new Currency(),
    }
  },
  computed: {
    listAccounts: function(){
      return _.orderBy(this.rawAccountsData, ['disabled', 'name'], ['asc', 'asc']);
    },
    listCurrencies: function(){
      return _.sortBy(this.currencyObject.list(), ['code']);
    },
    listInstitutions: function(){
      return _.sortBy(this.institutionObject.retrieve, 'name');
    },
    formDefaultData: function(){
      return {
        id: null,
        name: '',
        institutionId: 0,
        disabled: false,
        total: '',
        currency: null, // this gets set when component is mounted
        createStamp: '',
        modifiedStamp: '',
        disabledStamp: '',
      };
    },
  },
  methods: {
    decimaliseTotal: function(){
      if(!_.isEmpty(this.form.total)){
        this.form.total = this.decimaliseValue(this.form.total);
      }
    },
    makeDateReadable(isoDateString){
      return new Date(isoDateString).toString();
    },
    fillForm: function(account){
      this.form = _.clone(account);

      Object.keys(this.form).forEach(function(k){
        switch(k){
          case 'institution_id':
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
    setFormDefaults: function(){
      this.fillForm(this.formDefaultData);
      this.form.currency = this.currencyObject.default.code;
    },

    retrieveUpToDateAccountData: function(accountId = null){
      if(_.isNumber(accountId)){
        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

        new Promise(function(resolve, reject){
          let accountData = this.accountObject.find(accountId);
          if(this.accountObject.isDataUpToDate(accountData)){
            resolve(accountData);
          } else {
            reject(accountId);
          }
        }.bind(this))
          .then(this.fillForm.bind(this))
          .catch(function(accountId){
            this.accountObject.fetch(accountId)
              .then(function(fetchResult){
                let freshlyFetchedAccountData = {};
                if(fetchResult.fetched){
                  freshlyFetchedAccountData = this.accountObject.find(accountId);
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
    },
    save: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

      let accountData = {};
      Object.keys(this.form).forEach(function(formDatumKey){
        switch (formDatumKey){
          case 'id':
          case 'name':
          case 'disabled':
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
      this.accountObject.save(accountData)
        .then(function(notification){
          this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
          // show a notification if needed
          if(!_.isEmpty(notification)){
            this.$eventHub.broadcast(
                this.$eventHub.EVENT_NOTIFICATION,
                {type: notification.type, message: notification.message}
            );
          }
        }.bind(this))
        .finally(function(){
          this.setFormDefaults();
          this.accountsObject.setFetchedState = false;
          this.fetchAccounts();
        }.bind(this))
    }
  },
  mounted: function(){
    this.setFormDefaults();
  }
}
</script>

<style lang="scss" scoped>
@import "../../../sass/settings";

  .settings-account-currency{
    margin-top: 0.4rem;
  }
  .subtitle{
    margin-left: 3.5rem;
  }
</style>