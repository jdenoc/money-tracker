<template>
    <!--Account/Account-type-->
  <div v-bind:id="getIdForComponent" class="flex justify-between">
    <div class="flex flex-col mr-2">
      <!-- Account/Account-type toggle button -->
      <toggle-button
          class="account-or-account-type-toggle-button"
          v-bind="toggleButtonProperties"
          v-bind:toggle-state.sync="accountOrAccountTypeToggledFromProps"
      ></toggle-button>

      <!-- Account/Account-type display disabled checkbox -->
      <label class="show-disabled-accounts-or-account-types rounded-md py-0.5 px-2 mt-px inline-flex text-gray-700 text-xs"
          v-show="areDisabledAccountsOrAccountTypesPresent"
          v-bind:for="getIdForShowDisabledCheckbox"
      >
        <input type="checkbox" class="appearance-none hidden"
            v-bind:id="getIdForShowDisabledCheckbox"
            v-model="canShowDisabledAccountAndAccountTypes"
        />
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 -mt-px mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
           v-show="!canShowDisabledAccountAndAccountTypes"
        >
          <circle cx="50%" cy="50%" r="10" stroke-width="2"  />
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 -mt-px mr-1 text-green-600 opacity-90 hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"
           v-show="canShowDisabledAccountAndAccountTypes"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Show Disabled
      </label>
    </div>

    <!--Account/Account-type selector -->
    <div class="flex w-full">
      <div class="relative text-gray-700 w-full">
        <span class="loading absolute inset-y-3 left-2 w-full" v-show="!areAccountsAndAccountTypesSet">
          <svg class="animate-spin mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </span>

        <select name="select-account-or-account-types-id" class="select-account-or-account-types-id rounded min-w-60"
            v-model="accountOrAccountTypeIdFromProps"
            v-on:change="selectorValueChange"
        >
          <option value="" selected>[ ALL ]</option>
          <option
              v-for="accountOrAccountType in listProcessedAccountOrAccountTypes"
              v-show="!accountOrAccountType.disabled || canShowDisabledAccountAndAccountTypes"
              v-bind:key="accountOrAccountType.id"
              v-bind:value="accountOrAccountType.id"
              v-bind:class="{'disabled-option has-text-grey-light' : accountOrAccountType.disabled}"
              v-text="accountOrAccountType.name"
          ></option>
        </select>
      </div>
    </div>
  </div>
</template>

<script lang="js">
import _ from "lodash";
import {accountsObjectMixin} from "../mixins/accounts-object-mixin";
import {accountTypesObjectMixin} from "../mixins/account-types-object-mixin";
import {tailwindColorsMixin} from "../mixins/tailwind-colors-mixin";
import ToggleButton from "./toggle-button";

const EMIT_UPDATE_TOGGLE = 'update:accountOrAccountTypeToggled';
const EMIT_UPDATE_SELECT = 'update:accountOrAccountTypeId';

export default {
  name: "account-account-type-toggling-selector",
  components: {
    ToggleButton
  },
  mixins: [accountsObjectMixin, accountTypesObjectMixin, tailwindColorsMixin],
  props: {
    id: {type: String, required: true},
    accountOrAccountTypeToggled: {type: Boolean, default: true},
    accountOrAccountTypeId: {type: [String,Number], required: true, default: ''}
  },
  data: function(){
    return {
      canShowDisabledAccountAndAccountTypes: false,
      accountOrAccountTypeToggledFromProps: this.accountOrAccountTypeToggled, // toggle
      accountOrAccountTypeIdFromProps: this.accountOrAccountTypeId,  // select
    }
  },
  watch: {
    accountOrAccountTypeToggled: function(newValue){
      // update to prop.accountOrAccountTypeToggled (for toggle) from parent after init
      this.accountOrAccountTypeToggledFromProps = newValue;
    },
    accountOrAccountTypeId: function(newValue){
      // update to prop.accountOrAccountTypeId (for select) from parent after init
      this.accountOrAccountTypeIdFromProps = newValue;
    },
    accountOrAccountTypeToggledFromProps: function(newValue){
      // data.accountOrAccountTypeToggledFromProps updated
      this.resetAccountOrAccountTypeSelectValue();
      this.$emit(EMIT_UPDATE_TOGGLE, newValue);
    },
  },
  computed: {
    areAccountsAndAccountTypesSet: function(){
      return this.areAccountTypesAvailable && this.areAccountsAvailable;
    },
    areDisabledAccountsOrAccountTypesPresent: function(){
      return !_.isEmpty(this.listAccountOrAccountTypes)
        && this.listAccountOrAccountTypes
          .filter(function(accountOrAccountTypeObject){
            return accountOrAccountTypeObject.disabled
          }).length > 0;
    },

    getIdForComponent: function(){
      return 'account-or-account-type-toggling-selector-for-'+this.id;
    },
    getIdForShowDisabledCheckbox: function(){
      return 'show-disabled-accounts-or-account-types-'+this.id+'-checkbox';
    },
    getIdForToggleSwitch: function(){
      return 'toggle-account-and-account-types-for-'+this.id;
    },

    listProcessedAccountOrAccountTypes: function(){
      return this.processListOfObjects(this.listAccountOrAccountTypes, this.canShowDisabledAccountAndAccountTypes);
    },
    listAccountOrAccountTypes: function(){
      if(this.accountOrAccountTypeToggledFromProps === this.selectedToggleSwitch.account){
        return this.rawAccountsData;
      } else if(this.accountOrAccountTypeToggledFromProps === this.selectedToggleSwitch.accountType){
        return this.rawAccountTypesData;
      } else {
        return [];
      }
    },
    toggleButtonProperties: function(){
      return {
        colorChecked: this.tailwindColors.gray[400],
        colorUnchecked: this.tailwindColors.gray[400],
        fontSize: 12, // px
        height: 36, // px
        labelChecked: "Account",
        labelUnchecked: "Account Type",
        toggleId: this.getIdForToggleSwitch,
        width: 140, // px
      };
    },
    selectedToggleSwitch: function(){
      return {
        account: true,
        accountType: false
      }
    },
  },
  methods: {
    processListOfObjects: function(listOfObjects, canShowDisabled=true){
      if(!canShowDisabled){
        listOfObjects = listOfObjects.filter(function(object){
          return !object.disabled;
        });
      }
      return _.orderBy(listOfObjects, 'name');
    },
    resetAccountOrAccountTypeSelectValue: function(){
      this.accountOrAccountTypeIdFromProps = '';
      this.selectorValueChange();
    },
    selectorValueChange: function(){
      this.$emit(EMIT_UPDATE_SELECT, this.accountOrAccountTypeIdFromProps);
    },
  }
}
</script>

<style lang="scss" scoped>
</style>