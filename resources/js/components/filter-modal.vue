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
                    v-bind:account-or-account-type-id.sync="filterData.accountOrAccountTypeId"
                    v-on:update-select="filterData.accountOrAccountTypeId = $event"
                    v-bind:account-or-account-type-toggled.sync="filterData.accountOrAccountTypeSelected"
                    v-on:update-toggle="filterData.accountOrAccountTypeSelected = $event"
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
    import _ from 'lodash';
    import {Currency} from "../currency";
    import {accountsObjectMixin} from "../mixins/accounts-object-mixin";
    import {accountTypesObjectMixin} from "../mixins/account-types-object-mixin";
    import {bulmaColorsMixin} from "../mixins/bulma-colors-mixin";
    import {tagsObjectMixin} from "../mixins/tags-object-mixin";
    import {ToggleButton} from 'vue-js-toggle-button';
    import AccountAccountTypeTogglingSelector from "./account-account-type-toggling-selector";
    import Store from '../store';

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
            toggleButtonProperties: function(){
                return {
                    labels: {checked: 'Enabled', unchecked: 'Disabled'},
                    colors: {checked: this.colorInfo, unchecked: this.colorGreyLight},
                    height: 40,
                    width: 200,
                };
            },
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
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
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