<template>
    <div id="filter-modal" class="modal" v-bind:class="{'is-active': isVisible}">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Filter Entries</p>
                <button class="delete" aria-label="close" v-on:click="closeModal"></button>
            </header>

            <section class="modal-card-body">
                <div class="field is-horizontal">
                    <!--Start Date-->
                    <div class="field-label is-normal"><label class="label" for="filter-start-date">Start Date:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <input class="input has-text-grey-dark" id="filter-start-date" name="filter-start-date" type="date"
                           v-model="filterData.startDate"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--End Date-->
                    <div class="field-label is-normal"><label class="label" for="filter-end-date">End Date:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <input class="input has-text-grey-dark" id="filter-end-date" name="filter-end-date" type="date"
                           v-model="filterData.endDate"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Account/Account-type-->
                    <div class="field-label is-normal no-padding-top">
                        <div class="label">
                            <toggle-button
                                id="filter-account-account-types"
                                v-model="filterData.accountOrAccountTypeSelected"
                                v-bind:value="filterData.accountOrAccountTypeSelected"
                                v-bind:labels="{checked: 'Account', unchecked: 'Account Type'}"
                                v-bind:color="{checked: toggleButtonProperties.colors.unchecked, unchecked: toggleButtonProperties.colors.unchecked}"
                                v-bind:height="36"
                                v-bind:width="140"
                                v-bind:sync="true"
                                v-on:click.native="resetAccountOrAccountTypeField"
                            />
                        </div>
                        <div id="filter-show-disabled" v-show="areAccountsOrAccountTypesDisabled">
                            <input class="is-checkradio is-circle is-small" id="filter-show-disabled-checkbox" type="checkbox"
                               v-model="canShowDisabledAccountAndAccountTypes"
                            />
                            <label for="filter-show-disabled-checkbox">Show Disabled</label>
                        </div>
                    </div>
                    <div class="field-body"><div class="field">
                        <div class="control">
                            <div class="select" v-bind:class="{'is-loading': !areAccountsAndAccountTypesSet}">
                                <select name="filter-account-or-account-types-id" id="filter-account-or-account-types-id" class="has-text-grey-dark"
                                    v-model="filterData.accountOrAccountTypeId"
                                    v-on:change="updateAccountCurrency"
                                    >
                                    <option value="" selected>[ ALL ]</option>
                                    <option
                                        v-for="accountOrAccountType in listAccountOrAccountTypes"
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

                <div class="field is-horizontal">
                    <!--Tags-->
                    <div class="field-label is-normal"><label class="label">Tags:</label></div>
                    <div class="field-body"><div class="tags" id="filter-tags" v-bind:class="{'is-loading': !areTagsSet}">
                        <!--TODO: make all values collapsible-->
                        <div class="field" v-for="tag in listTags">
                            <input class="is-checkradio is-block is-info" type="checkbox" name="filter-tag[]"
                               v-bind:id="'filter-tag-'+tag.id"
                               v-bind:value="tag.id"
                               v-bind:checked="filterData.tags[tag.id]"
                               v-model="filterData.tags[tag.id]"
                            />
                            <label class="checkbox-adjusted-top" v-bind:for="'filter-tag-'+tag.id" v-text="tag.name"></label>
                        </div>
                    </div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Income-->
                    <div class="field-label is-normal"><label class="label" for="filter-is-income">Income:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <toggle-button
                            id="filter-is-income"
                            v-model="filterData.isIncome"
                            v-bind:value="filterData.isIncome"
                            v-bind:labels="toggleButtonProperties.labels"
                            v-bind:color="toggleButtonProperties.colors"
                            v-bind:height="toggleButtonProperties.height"
                            v-bind:width="toggleButtonProperties.width"
                            v-bind:sync="true"
                            v-on:click.native="flipCompanionSwitch('isExpense')"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Expense-->
                    <div class="field-label is-normal"><label class="label" for="filter-is-expense">Expense:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <toggle-button
                            id="filter-is-expense"
                            v-model="filterData.isExpense"
                            v-bind:value="filterData.isExpense"
                            v-bind:labels="toggleButtonProperties.labels"
                            v-bind:color="toggleButtonProperties.colors"
                            v-bind:height="toggleButtonProperties.height"
                            v-bind:width="toggleButtonProperties.width"
                            v-bind:sync="true"
                            v-on:click.native="flipCompanionSwitch('isIncome')"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Has Attachment(s)-->
                    <div class="field-label is-normal"><label class="label" for="filter-has-attachment">Has Attachment(s):</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <toggle-button
                            id="filter-has-attachment"
                            v-model="filterData.hasAttachment"
                            v-bind:value="filterData.hasAttachment"
                            v-bind:labels="toggleButtonProperties.labels"
                            v-bind:color="toggleButtonProperties.colors"
                            v-bind:height="toggleButtonProperties.height"
                            v-bind:width="toggleButtonProperties.width"
                            v-bind:sync="true"
                            v-on:click.native="flipCompanionSwitch('noAttachment')"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--No Attachment(s)-->
                    <div class="field-label is-normal"><label class="label" for="filter-no-attachment">No Attachment(s):</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <toggle-button
                            id="filter-no-attachment"
                            v-model="filterData.noAttachment"
                            v-bind:value="filterData.noAttachment"
                            v-bind:labels="toggleButtonProperties.labels"
                            v-bind:color="toggleButtonProperties.colors"
                            v-bind:height="toggleButtonProperties.height"
                            v-bind:width="toggleButtonProperties.width"
                            v-bind:sync="true"
                            v-on:click.native="flipCompanionSwitch('hasAttachment')"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Transfer-->
                    <div class="field-label is-normal"><label class="label" for="filter-is-transfer">Transfer:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <toggle-button
                            id="filter-is-transfer"
                            v-model="filterData.isTransfer"
                            v-bind:value="filterData.isTransfer"
                            v-bind:labels="toggleButtonProperties.labels"
                            v-bind:color="toggleButtonProperties.colors"
                            v-bind:height="toggleButtonProperties.height"
                            v-bind:width="toggleButtonProperties.width"
                            v-bind:sync="true"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Unconfirmed-->
                    <div class="field-label is-normal"><label class="label" for="filter-unconfirmed">Not Confirmed:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <toggle-button
                            id="filter-unconfirmed"
                            v-model="filterData.unconfirmed"
                            v-bind:value="filterData.unconfirmed"
                            v-bind:labels="toggleButtonProperties.labels"
                            v-bind:color="toggleButtonProperties.colors"
                            v-bind:height="toggleButtonProperties.height"
                            v-bind:width="toggleButtonProperties.width"
                            v-bind:sync="true"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Min Range-->
                    <div class="field-label is-normal"><label class="label" for="filter-min-value">Min Range:</label></div>
                    <div class="field-body"><div class="field"><div class="control has-icons-left">
                        <input class="input has-text-grey-dark" id="filter-min-value" name="filter-min-value" type="text" placeholder="999.99"
                            v-model="filterData.minValue"
                            v-on:change="decimaliseValue('minValue')"
                        />
                        <span class="icon is-left"><i class="fas" v-bind:class="accountCurrencyClass"></i></span>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Max Range:                      | [input]-->
                    <div class="field-label is-normal"><label class="label" for="filter-max-value">Max Range:</label></div>
                    <div class="field-body"><div class="field"><div class="control has-icons-left">
                        <input class="input has-text-grey-dark" id="filter-max-value" name="filter-max-value" type="text" placeholder="999.99"
                               v-model="filterData.maxValue"
                               v-on:change="decimaliseValue('maxValue')"
                        />
                        <span class="icon is-left"><i class="fas" v-bind:class="accountCurrencyClass"></i></span>
                    </div></div></div>
                </div>
            </section>

            <footer class="modal-card-foot">
                <div class="container">
                    <div class="field is-grouped">
                        <div class="control is-expanded"></div>
                        <div class="control">
                            <button type="button" id="filter-cancel-btn" class="button" v-on:click="closeModal">Cancel</button>
                            <button type="button" id="filter-reset-btn" class="button is-warning" v-on:click="resetFields">
                                <i class="fas fa-undo-alt has-padding-right"></i>Reset
                            </button>
                            <button type="button" id="filter-btn" class="button is-primary" v-on:click="makeFilterRequest">
                                <i class="fas fa-search has-padding-right"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import {Accounts} from '../accounts';
    import {AccountTypes} from '../account-types';
    import {Tags} from '../tags';
    import { ToggleButton } from 'vue-js-toggle-button'
    import Store from '../store';

    export default {
        name: "filter-modal",
        components: {
            ToggleButton,
        },
        data: function(){
            return {
                accountsObject: new Accounts(),
                accountTypesObject: new AccountTypes(),
                tagsObject: new Tags(),

                isVisible: false,

                accountCurrencyClass: "fa-dollar-sign",

                switchValueAccount: true,
                switchValueAccountType: false,

                canShowDisabledAccountAndAccountTypes: false,

                filterData: {},
                defaultData: {
                    startDate: "",
                    endDate: "",
                    accountOrAccountTypeSelected: null, // will be set by resetFields
                    accountOrAccountTypeId: "",
                    tags: [],
                    isIncome: false,
                    isExpense: false,
                    hasAttachment: false,
                    noAttachment: false,
                    isTransfer: false,
                    unconfirmed: false,
                    minValue: "",
                    maxValue: ""
                },

                currency: {
                    euro:     {label: "EUR", class: "fa-euro-sign"},
                    dollarUs: {label: "USD", class: "fa-dollar-sign"},
                    dollarCa: {label: "CAD", class: "fa-dollar-sign"},
                    pound:    {label: "GBP", class: "fa-pound-sign"}
                },

                toggleButtonProperties: {
                    labels: {checked: 'Enabled', unchecked: 'Disabled'},
                    colors: {checked: '#209CEE', unchecked: '#B5B5B5'},
                    height: 40,
                    width: 200,
                },
            }
        },
        computed: {
            areAccountsAndAccountTypesSet: function(){
                return this.listAccountTypes.length > 0 && this.listAccounts.length > 0;
            },
            areTagsSet: function(){
                return !_.isEmpty(this.listTags);
            },
            areAccountsOrAccountTypesDisabled: function(){
                let accountOrAccountTypeobjects = {};
                if(this.filterData.accountOrAccountTypeSelected === this.switchValueAccount){
                    accountOrAccountTypeobjects = this.accountsObject.retrieve;
                } else if(this.filterData.accountOrAccountTypeSelected === this.switchValueAccountType){
                    accountOrAccountTypeobjects = this.accountTypesObject.retrieve;
                }

                return !_.isEmpty(accountOrAccountTypeobjects)
                    && this.processListOfObjects(accountOrAccountTypeobjects)
                        .filter(function(accountOrAccountTypeObject){
                            return accountOrAccountTypeObject.disabled
                        }).length > 0;
            },
            listAccountOrAccountTypes: function(){
                if(this.filterData.accountOrAccountTypeSelected === this.switchValueAccount){
                    return this.listAccounts
                } else if(this.filterData.accountOrAccountTypeSelected === this.switchValueAccountType){
                    return this.listAccountTypes
                }
            },
            listAccountTypes: function(){
                let accountTypes = this.accountTypesObject.retrieve;
                return this.processListOfObjects(accountTypes, this.canShowDisabledAccountAndAccountTypes);
            },
            listAccounts: function(){
                let accounts = this.accountsObject.retrieve;
                return this.processListOfObjects(accounts, this.canShowDisabledAccountAndAccountTypes);
            },
            listTags: function(){
                let tags = this.tagsObject.retrieve;
                return this.processListOfObjects(tags);
            }
        },
        methods: {
            setModalState: function(modal){
                Store.dispatch('currentModal', modal);
            },
            openModal: function(){
                this.setModalState(Store.getters.STORE_MODAL_FILTER);
                this.isVisible = true;
            },
            closeModal: function(){
                this.setModalState(Store.getters.STORE_MODAL_NONE);
                this.isVisible = false;
            },
            updateAccountCurrency: function(){
                let account = null;
                if(this.filterData.accountOrAccountTypeSelected === this.switchValueAccount){
                    account = this.accountsObject.find(this.filterData.accountOrAccountTypeId);
                } else if(this.filterData.accountOrAccountTypeSelected === this.switchValueAccountType){
                    account = this.accountTypesObject.getAccount(this.filterData.accountOrAccountTypeId);
                }

                switch(account.currency){
                    case this.currency.euro.label:
                        this.accountCurrencyClass = this.currency.euro.class;
                        break;

                    case this.currency.pound.label:
                        this.accountCurrencyClass = this.currency.pound.class;
                        break;

                    case this.currency.dollarCa.label:
                        this.accountCurrencyClass = this.currency.dollarCa.class;
                        break;

                    case this.currency.dollarUs.label:
                    default:
                        this.accountCurrencyClass = this.currency.dollarUs.class;
                }
            },
            resetAccountOrAccountTypeField: function(){
                this.filterData.accountOrAccountTypeId = this.defaultData.accountOrAccountTypeId;
                this.accountCurrencyClass = this.currency.dollarUs.class;
            },
            resetFields: function(){
                this.defaultData.accountOrAccountTypeSelected = this.switchValueAccount;
                this.resetAccountOrAccountTypeField();
                for(let t in this.listTags){
                    this.defaultData.tags[this.listTags[t]['id']] = false;
                }
                this.filterData = _.clone(this.defaultData);
            },
            flipCompanionSwitch: function(companionFilter){
                if(this.filterData[companionFilter] === true){
                    this.filterData[companionFilter] = false;
                }
            },
            decimaliseValue: function(valueField){
                if(!_.isEmpty(this.filterData[valueField])){
                    let cleanedValue = this.filterData[valueField].replace(/[^0-9.]/g, '');
                    this.filterData[valueField] = parseFloat(cleanedValue).toFixed(2);
                }
            },
            makeFilterRequest: function(){
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
                this.closeModal();

                let filterDataParameters = {};

                if(!_.isEmpty(this.filterData.startDate)){
                    filterDataParameters.start_date = this.filterData.startDate;
                }
                if(!_.isEmpty(this.filterData.endDate)){
                    filterDataParameters.end_date = this.filterData.endDate;
                }

                if(this.filterData.accountOrAccountTypeSelected === this.switchValueAccount){
                    filterDataParameters.account = this.filterData.accountOrAccountTypeId;
                } else if(this.filterData.accountOrAccountTypeSelected === this.switchValueAccountType){
                    filterDataParameters.account_type = this.filterData.accountOrAccountTypeId;
                }

                if(_.isArray(this.filterData.tags)){
                    filterDataParameters.tags = [];
                    this.filterData.tags.forEach(function(isSelected, tagId){
                        if(isSelected){
                            filterDataParameters.tags.push(tagId);
                        }
                    });
                }

                if(this.filterData.isIncome){
                    filterDataParameters.expense = false;
                } else if(this.filterData.isExpense){
                    filterDataParameters.expense = true;
                }

                if(this.filterData.hasAttachment){
                    filterDataParameters.attachments = true;
                } else if(this.filterData.noAttachment){
                    filterDataParameters.attachments = false;
                }

                if(this.filterData.isTransfer){
                    filterDataParameters.is_transfer = true;
                }

                if(this.filterData.unconfirmed){
                    filterDataParameters.unconfirmed = true;
                }

                if(!_.isEmpty(this.filterData.minValue)){
                    filterDataParameters.min_value = this.filterData.minValue;
                }
                if(!_.isEmpty(this.filterData.maxValue)){
                    filterDataParameters.max_value = this.filterData.maxValue;
                }

                this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, {pageNumber: 0, filterParameters: filterDataParameters});
            },
            processListOfObjects: function(listOfObjects, canShowDisabled=true){
                if(!canShowDisabled){
                    listOfObjects = listOfObjects.filter(function(object){
                        return !object.disabled;
                    });
                }
                return _.orderBy(listOfObjects, 'name');
            }
        },
        created: function(){
            this.$eventHub.listen(this.$eventHub.EVENT_FILTER_MODAL_OPEN, this.openModal);
            this.$eventHub.listen(this.$eventHub.EVENT_FILTER_MODAL_CLOSE, this.closeModal);
        },
        mounted: function(){
            this.resetFields();
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
#filter-account-or-account-types-id{
    min-width: 16rem;
}
.tags{
    margin: 0;

    .field {
        margin: $quarter-margin 0;

        label.checkbox-adjusted-top {
            margin: $quarter-margin;
            padding-right: 0.5rem;
        }
    }
}

#filter-show-disabled{
    margin-top: -0.25rem;
    margin-bottom: -0.5rem;

    #filter-show-disabled-checkbox+label:after,
    #filter-show-disabled-checkbox+label::after{
        top: 0.3125rem;
    }
}
</style>