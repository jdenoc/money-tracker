<template>
    <div class="field is-horizontal">
        <!--Account/Account-type-->
        <div class="field-label is-normal no-padding-top">
            <div class="label">
                <toggle-button
                    id="toggle-account-account-types"
                    v-model="propToggle"
                    v-bind:value="propToggle"
                    v-bind:labels="{checked: toggleButtonProperties.label.checked, unchecked: toggleButtonProperties.label.unchecked}"
                    v-bind:color="{checked: toggleButtonProperties.colors.checked, unchecked: toggleButtonProperties.colors.unchecked}"
                    v-bind:height="toggleButtonProperties.height"
                    v-bind:width="toggleButtonProperties.width"
                    v-bind:sync="true"
                />
            </div>
            <div id="show-disabled-accounts-account-types" v-show="areDisabledAccountsOrAccountTypesPresent">
                <input class="is-checkradio is-circle is-small" id="show-disabled-checkbox" type="checkbox"
                   v-model="canShowDisabledAccountAndAccountTypes"
                />
                <label for="show-disabled-checkbox">Show Disabled</label>
            </div>
        </div>
        <div class="field-body"><div class="field">
            <div class="control">
                <div class="select" v-bind:class="{'is-loading': !areAccountsAndAccountTypesSet}">
                    <select name="select-account-or-account-types-id" id="select-account-or-account-types-id" class="has-text-grey-dark"
                        v-model="propSelect"
                        v-on:change="selectorValueChange"
                    >
                        <option value="" selected>[ ALL ]</option>
                        <option
                            v-for="accountOrAccountType in listProcessedAccountOrAccountTypes"
                            v-show="!accountOrAccountType.disabled || canShowDisabledAccountAndAccountTypes"
                            v-bind:key="accountOrAccountType.id"
                            v-bind:value="accountOrAccountType.id"
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
    import {Accounts} from "../accounts";
    import {AccountTypes} from "../account-types";
    import {ToggleButton} from 'vue-js-toggle-button'

    export default {
        name: "account-account-type-toggling-selector",
        components: {
            ToggleButton
        },
        props: {
            accountOrAccountTypeToggled: {type: Boolean, default: true},
            accountOrAccountTypeId: {type: String|Number, required: true, default: ''}
        },
        data: function(){
            return {
                accountsObject: new Accounts(),
                accountTypesObject: new AccountTypes(),

                selectedToggleSwitch: {
                    account: true,
                    accountType: false
                },

                toggleButtonProperties: {
                    label: {checked: "Account", unchecked: "Account Type"},
                    colors: {checked: '#B5B5B5', unchecked: '#B5B5B5'},
                    height: 36,
                    width: 140,
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
            }
        },
        computed: {
            listProcessedAccountOrAccountTypes: function(){
                return this.processListOfObjects(this.listAccountOrAccountTypes, this.canShowDisabledAccountAndAccountTypes);
            },
            listAccountOrAccountTypes: function(){
                if(this.propToggle === this.selectedToggleSwitch.account){
                    return this.listAccounts;
                } else if(this.propToggle === this.selectedToggleSwitch.accountType){
                    return this.listAccountTypes
                }
            },
            listAccountTypes: function(){
                return this.accountTypesObject.retrieve;
            },
            listAccounts: function(){
                return this.accountsObject.retrieve;
            },

            areAccountsAndAccountTypesSet: function(){
                return this.listAccountTypes.length > 0 && this.listAccounts.length > 0;
            },
            areDisabledAccountsOrAccountTypesPresent: function(){
                return !_.isEmpty(this.listAccountOrAccountTypes)
                    && this.processListOfObjects(this.listAccountOrAccountTypes)
                        .filter(function(accountOrAccountTypeObject){
                            return accountOrAccountTypeObject.disabled
                        }).length > 0;
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
    #select-account-or-account-types-id{
        min-width: 16rem;
    }

    #show-disabled-accounts-account-types{
        margin-top: -0.25rem;
        margin-bottom: -0.5rem;

        +label:after,
        +label::after{
            top: 0.3125rem;
        }
    }
</style>