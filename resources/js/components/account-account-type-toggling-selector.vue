<template>
    <div class="field is-horizontal" v-bind:id="getIdForComponent">
        <div class="field-label is-normal no-padding-top">
            <div class="label">
              <!--Account/Account-type toggle button-->
              <ToggleButton
                  v-bind="toggleButtonProperties"
                  v-model:toggle-state="toggleButtonState"
                  class="account-or-account-type-toggle-button"
                  v-on:click="resetAccountOrAccountTypeSelectValue"
              ></ToggleButton>
            </div>
            <!--Account/Account-type display disabled checkbox -->
            <div class="show-disabled-accounts-or-account-types" v-show="areDisabledAccountsOrAccountTypesPresent">
                <input class="is-checkradio is-circle is-small" type="checkbox"
                   v-bind:id="getIdForShowDisabledCheckbox"
                   v-model="canShowDisabledAccountAndAccountTypes"
                />
                <label v-bind:for="getIdForShowDisabledCheckbox">Show Disabled</label>
            </div>
        </div>
        <div class="field-body"><div class="field">
            <div class="control">
                <!--Account/Account-type selector -->
                <div class="select" v-bind:class="{'is-loading': !areAccountsAndAccountTypesSet}">
                    <select name="select-account-or-account-types-id" class="has-text-grey-dark select-account-or-account-types-id"
                        v-model="selectorValue"
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
        </div></div>
    </div>
</template>

<script lang="js">
    import _ from "lodash";
    import {accountsObjectMixin} from "../mixins/accounts-object-mixin";
    import {accountTypesObjectMixin} from "../mixins/account-types-object-mixin";
    import {bulmaColorsMixin} from "../mixins/bulma-colors-mixin";
    import ToggleButton from './toggle-button';

    const EMIT_ACCOUNT_OR_ACCOUNT_TYPE_ID = 'update:accountOrAccountTypeId';
    const EMIT_ACCOUNT_OR_ACCOUNT_TYPE_TOGGLE = 'update:accountOrAccountTypeToggled';

    export default {
        name: "account-account-type-toggling-selector",
        components: {
            ToggleButton
        },
        mixins: [accountsObjectMixin, accountTypesObjectMixin, bulmaColorsMixin],
        emits:{
          [EMIT_ACCOUNT_OR_ACCOUNT_TYPE_ID]: function(payload){
            return typeof payload === 'number' || payload === null;
          },
          [EMIT_ACCOUNT_OR_ACCOUNT_TYPE_TOGGLE]: function(payload){
            return typeof payload === 'boolean';
          }
        },
        props: {
            id: {type: String, required: true},
            accountOrAccountTypeToggled: {type: Boolean, default: true},
            accountOrAccountTypeId: {type: Number, default: null}
        },
        data: function(){
            return {
                selectedToggleSwitch: {
                    account: true,
                    accountType: false
                },
                canShowDisabledAccountAndAccountTypes: false,
            }
        },
        computed: {
          toggleButtonState: {
            get: function(){
              return this.accountOrAccountTypeToggled;
            },
            set: function(value){
              this.$emit(EMIT_ACCOUNT_OR_ACCOUNT_TYPE_TOGGLE, value);
            }
          },
          selectorValue: {
            get: function(){
              return _.isNull(this.accountOrAccountTypeId) ? '' : this.accountOrAccountTypeId;
            },
            set: function(value){
              value = value === '' ? null : value;
              this.$emit(EMIT_ACCOUNT_OR_ACCOUNT_TYPE_ID, value);
            }
          },

            listProcessedAccountOrAccountTypes: function(){
                return this.processListOfObjects(this.listAccountOrAccountTypes, this.canShowDisabledAccountAndAccountTypes);
            },
            listAccountOrAccountTypes: function(){
                if(this.toggleButtonState === this.selectedToggleSwitch.account){
                    return this.rawAccountsData;
                } else if(this.toggleButtonState === this.selectedToggleSwitch.accountType){
                    return this.rawAccountTypesData;
                }
            },

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

            toggleButtonProperties: function(){
                return {
                  buttonName: this.getIdForToggleSwitch,
                  labelChecked: "Account",
                  labelUnchecked: "Account Type"
                };
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
            this.selectorValue = null;
          }
        }
    }
</script>

<style lang="scss" scoped>
    .field-label{
        &.is-normal{
            font-size: 0.85rem;
            margin-right: 0.75rem;
            padding-top: 0.5rem;

            &.no-padding-top{
                padding-top: 0;
            }
        }

        .label{
            width: 10rem;
        }
    }

    .select-account-or-account-types-id{
        min-width: 15rem;
        max-width: 19rem;
    }

    .show-disabled-accounts-or-account-types{
        margin-top: -0.25rem;
        margin-bottom: -0.6rem;

        +label:after,
        +label::after{
            top: 0.3125rem;
        }
    }

    // accounts/account-types toggle button
    @import '~bulma/sass/helpers/color';
    $toggle-button-bg-color: $grey-light;
    $toggle-button-text-color: $white;
    .account-or-account-type-toggle-button{
      --toggle-width: 8.5rem;
      --toggle-font-size: 0.8;

      // accounts
      --toggle-bg-on: #{$toggle-button-bg-color};
      --toggle-border-on: #{$toggle-button-bg-color};
      --toggle-text-on: #{$toggle-button-text-color};
      // account-types
      --toggle-bg-off: #{$toggle-button-bg-color};
      --toggle-border-off: #{$toggle-button-bg-color};
      --toggle-text-off: #{$toggle-button-text-color};
    }
</style>