<template>
    <div class="field is-horizontal" v-bind:id="getIdForComponent">
        <!--Account/Account-type-->
        <div class="field-label is-normal no-padding-top">
            <div class="label">
                <toggle-button
                    v-bind:id="getIdForToggleSwitch"
                    v-model="propToggle"
                    v-bind:value="propToggle"
                    v-bind:labels="{checked: toggleButtonProperties.label.checked, unchecked: toggleButtonProperties.label.unchecked}"
                    v-bind:color="{checked: toggleButtonProperties.colors.checked, unchecked: toggleButtonProperties.colors.unchecked}"
                    v-bind:height="toggleButtonProperties.height"
                    v-bind:width="toggleButtonProperties.width"
                    v-bind:sync="true"
                />
            </div>
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
    import {ToggleButton} from 'vue-js-toggle-button'

    export default {
        name: "account-account-type-toggling-selector",
        components: {
            ToggleButton
        },
        mixins: [accountsObjectMixin, accountTypesObjectMixin],
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
            }

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
</style>