<template>
    <div class="field is-horizontal" v-bind:id="getIdForComponent">
        <!--Account/Account-type-->
        <div class="field-label is-normal no-padding-top">
            <div class="label">
                <toggle-button
                    v-bind:id="getIdForToggleSwitch"
                    v-model="propToggle"
                    v-bind:value="propToggle"
                    v-bind:labels="toggleButtonProperties.label"
                    v-bind:color="toggleButtonProperties.colors"
                    v-bind:height="toggleButtonProperties.height"
                    v-bind:width="toggleButtonProperties.width"
                    v-bind:sync="true"
                />
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
                        v-model="propSelect"
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
        </div></div>
    </div>
</template>

<script>
    import _ from "lodash";
    import {accountsObjectMixin} from "../mixins/accounts-object-mixin";
    import {accountTypesObjectMixin} from "../mixins/account-types-object-mixin";
    import {bulmaColorsMixin} from "../mixins/bulma-colors-mixin";
    import {ToggleButton} from 'vue-js-toggle-button'

    export default {
        name: "account-account-type-toggling-selector",
        components: {
            ToggleButton
        },
        mixins: [accountsObjectMixin, accountTypesObjectMixin, bulmaColorsMixin],
        props: {
            id: {type: String, required: true},
            accountOrAccountTypeToggled: {type: Boolean, default: true},
            accountOrAccountTypeId: {type: String|Number, required: true, default: ''}
        },
        data: function(){
            return {
                selectedToggleSwitch: {
                    account: true,
                    accountType: false
                },

                propToggle: this.accountOrAccountTypeToggled,
                propSelect: this.accountOrAccountTypeId,

                canShowDisabledAccountAndAccountTypes: false,
            }
        },
        watch: {
            accountOrAccountTypeToggled: function(newValue, oldValue){
                this.propToggle = newValue;
            },
            accountOrAccountTypeId: function(newValue, oldValue){
                this.propSelect = newValue;
            },
            propToggle: function(newValue, oldValue){
                this.resetAccountOrAccountTypeSelectValue();
                this.$emit('update-toggle', newValue);
            },
        },
        computed: {
            listProcessedAccountOrAccountTypes: function(){
                return this.processListOfObjects(this.listAccountOrAccountTypes, this.canShowDisabledAccountAndAccountTypes);
            },
            listAccountOrAccountTypes: function(){
                if(this.propToggle === this.selectedToggleSwitch.account){
                    return this.rawAccountsData;
                } else if(this.propToggle === this.selectedToggleSwitch.accountType){
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
                    label: {checked: "Account", unchecked: "Account Type"},
                    colors: {checked: this.colorGreyLight, unchecked: this.colorGreyLight},
                    height: 36,
                    width: 140,
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
                this.propSelect = '';
                this.selectorValueChange();
            },
            selectorValueChange: function(){
                this.$emit('update-select', this.propSelect);
            }
        }
    }
</script>

<style lang="scss" scoped>
    $font-size: 0.85rem;
    $quarter-margin: 0.25rem;

    .field-label{
        &.is-normal{
            font-size: $font-size;
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

    .vue-js-switch{
        font-size: $font-size;
    }
    .select-account-or-account-types-id{
        min-width: 15rem;
        max-width: 19rem;
    }

    .show-disabled-accounts-or-account-types{
        margin-top: -0.25rem;
        margin-bottom: -0.5rem;

        +label:after,
        +label::after{
            top: 0.3125rem;
        }
    }


    // accounts/account-types toggle button
    @import '~bulma/sass/helpers/color';
    $toggle-button-bg-color: $grey-light;
    $toggle-button-text-color: $white;
    .account-or-account-type-toggle-button {
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