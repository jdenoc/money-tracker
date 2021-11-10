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

                <account-account-type-toggling-selector
                    id="filter-modal"
                    v-model:account-or-account-type-id="filterData.accountOrAccountTypeId"
                    v-model:account-or-account-type-toggled="filterData.accountOrAccountTypeSelected"
                ></account-account-type-toggling-selector>

                <div class="field is-horizontal">
                    <!--Tags-->
                    <div class="field-label is-normal"><label class="label">Tags:</label></div>
                    <div class="field-body"><div class="tags" id="filter-tags" v-bind:class="{'is-loading': !areTagsSet}">
                        <!--TODO: make all values collapsible-->
                        <div class="field" v-for="tag in listTags">
                            <input class="is-checkradio is-block is-info has-no-border" type="checkbox" name="filter-tag[]"
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
                      <ToggleButton
                          button-name="filter-is-income"
                          class="filter-toggle-switch"
                          v-model:toggle-state="filterData.isIncome"
                          v-on:click="flipCompanionSwitch('isExpense')"
                      ></ToggleButton>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Expense-->
                    <div class="field-label is-normal"><label class="label" for="filter-is-expense">Expense:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                      <ToggleButton
                          button-name="filter-is-expense"
                          class="filter-toggle-switch"
                          v-model:toggle-state="filterData.isExpense"
                          v-on:click="flipCompanionSwitch('isIncome')"
                      ></ToggleButton>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Has Attachment(s)-->
                    <div class="field-label is-normal"><label class="label" for="filter-has-attachment">Has Attachment(s):</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                      <ToggleButton
                          button-name="filter-has-attachment"
                          class="filter-toggle-switch"
                          v-model:toggle-state="filterData.hasAttachment"
                          v-on:click="flipCompanionSwitch('noAttachment')"
                      ></ToggleButton>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--No Attachment(s)-->
                    <div class="field-label is-normal"><label class="label" for="filter-no-attachment">No Attachment(s):</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                      <ToggleButton
                          button-name="filter-no-attachment"
                          class="filter-toggle-switch"
                          v-model:toggle-state="filterData.noAttachment"
                          v-on:click="flipCompanionSwitch('hasAttachment')"
                      ></ToggleButton>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Transfer-->
                    <div class="field-label is-normal"><label class="label" for="filter-is-transfer">Transfer:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                      <ToggleButton
                          button-name="filter-is-transfer"
                          class="filter-toggle-switch"
                          v-model:toggle-state="filterData.isTransfer"
                      ></ToggleButton>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Unconfirmed-->
                    <div class="field-label is-normal"><label class="label" for="filter-unconfirmed">Not Confirmed:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                      <ToggleButton
                          button-name="filter-unconfirmed"
                          class="filter-toggle-switch"
                          v-model:toggle-state="filterData.unconfirmed"
                      ></ToggleButton>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Min Range-->
                    <div class="field-label is-normal"><label class="label" for="filter-min-value">Min Range:</label></div>
                    <div class="field-body"><div class="field"><div class="control has-icons-left">
                        <input class="input has-text-grey-dark" id="filter-min-value" name="filter-min-value" type="text" placeholder="999.99" autocomplete="off"
                            v-model="filterData.minValue"
                            v-on:change="decimaliseValue('minValue')"
                        />
                        <span class="icon is-left"><i class="fas" v-bind:class="accountCurrencyClass"></i></span>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <!--Max Range-->
                    <div class="field-label is-normal"><label class="label" for="filter-max-value">Max Range:</label></div>
                    <div class="field-body"><div class="field"><div class="control has-icons-left">
                        <input class="input has-text-grey-dark" id="filter-max-value" name="filter-max-value" type="text" placeholder="999.99" autocomplete="off"
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
  // utilities
  import _ from 'lodash';
  import {store} from '../store';
  // objects
  import {Currency} from "../currency";
  import {accountsObjectMixin} from "../mixins/accounts-object-mixin";
  import {accountTypesObjectMixin} from "../mixins/account-types-object-mixin";
  import {bulmaColorsMixin} from "../mixins/bulma-colors-mixin";
  import {tagsObjectMixin} from "../mixins/tags-object-mixin";
  // components
  import AccountAccountTypeTogglingSelector from "./account-account-type-toggling-selector";
  import ToggleButton from './toggle-button';

    export default {
        name: "filter-modal",
        mixins: [accountsObjectMixin, accountTypesObjectMixin, bulmaColorsMixin, tagsObjectMixin],
        components: {
          AccountAccountTypeTogglingSelector,
          ToggleButton,
        },
        data: function(){
            return {
                currencyObject: new Currency(),

                isVisible: false,

                accountAccountTypeToggle: {
                    accountValue: true,
                    accountTypeValue: false
                },

                canShowDisabledAccountAndAccountTypes: false,

                filterData: {},
                defaultData: {
                    startDate: "",
                    endDate: "",
                    accountOrAccountTypeSelected: null, // will be set by resetFields
                    accountOrAccountTypeId: null,
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
            }
        },
        computed: {
            listTags: function(){
                return this.processListOfObjects(this.rawTagsData);
            },
            accountCurrencyClass: function(){
                let account = null;
                if(this.filterData.accountOrAccountTypeSelected === this.accountAccountTypeToggle.accountValue){
                    account = this.accountsObject.find(this.filterData.accountOrAccountTypeId);
                } else if(this.filterData.accountOrAccountTypeSelected === this.accountAccountTypeToggle.accountTypeValue){
                    account = this.accountTypesObject.getAccount(this.filterData.accountOrAccountTypeId);
                }

                let currencyCode = _.isNull(account) ? '' : account.currency;
                return this.currencyObject.getClassFromCode(currencyCode);
            },
        },
        methods: {
            setModalState: function(modal){
                store.dispatch('currentModal', modal);
            },
            openModal: function(){
                this.setModalState(store.getters.STORE_MODAL_FILTER);
                this.isVisible = true;
            },
            closeModal: function(){
                this.setModalState(store.getters.STORE_MODAL_NONE);
                this.isVisible = false;
            },
            resetFields: function(){
                this.defaultData.accountOrAccountTypeSelected = this.accountAccountTypeToggle.accountValue;
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
                this.$eventBus.broadcast(this.$eventBus.EVENT_LOADING_SHOW());
                this.closeModal();

                let filterDataParameters = {};

                if(!_.isEmpty(this.filterData.startDate)){
                    filterDataParameters.start_date = this.filterData.startDate;
                }
                if(!_.isEmpty(this.filterData.endDate)){
                    filterDataParameters.end_date = this.filterData.endDate;
                }

                if(this.filterData.accountOrAccountTypeSelected === this.accountAccountTypeToggle.accountValue){
                    filterDataParameters.account = this.filterData.accountOrAccountTypeId;
                } else if(this.filterData.accountOrAccountTypeSelected === this.accountAccountTypeToggle.accountTypeValue){
                    filterDataParameters.account_type = this.filterData.accountOrAccountTypeId;
                }

                if(_.isArray(this.filterData.tags)){
                    filterDataParameters.tags = [];
                    this.filterData.tags.forEach(function(isSelected, tagId){
                        if(isSelected){
                            filterDataParameters.tags.push(tagId);
                        }
                    });
                    if(_.isEmpty(filterDataParameters.tags)){
                        delete filterDataParameters.tags;
                    }
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

                this.$eventBus.broadcast(this.$eventBus.EVENT_ENTRY_TABLE_UPDATE(), {pageNumber: 0, filterParameters: filterDataParameters});
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
            this.$eventBus.listen(this.$eventBus.EVENT_FILTER_MODAL_OPEN(), this.openModal);
            this.$eventBus.listen(this.$eventBus.EVENT_FILTER_MODAL_CLOSE(), this.closeModal);
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

.filter-toggle-switch{
  --toggle-font-size: 1rem;
}
</style>