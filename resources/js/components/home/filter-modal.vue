<template>
  <!-- container/background -->
  <div id="filter-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"
      v-show="isVisible"
  >
    <!-- modal content -->
    <div class="modal relative top-20 mx-auto p-2 border w-160 shadow-lg rounded-md bg-white">
      <!-- HEADER -->
      <header class="modal-header border-b border-gray-200 px-2 py-3 flex justify-between bg-gray-50">
        <div class="flex text-xl font-medium flex">Filter Entries</div>

        <div class="flex">
          <button type="button" class="text-gray-400 hover:text-gray-600 ml-1" aria-label="close"
              v-on:click="closeModal"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </header>

      <!-- BODY -->
      <section class="modal-body p-3 grid grid-cols-4 gap-y-2 gap-x-4">
        <!--Start Date-->
        <label for="filter-start-date" class="text-sm font-medium justify-self-end py-1 my-0.5">Start Date:</label>
        <input id="filter-start-date" name="filter-start-date" type="date" class="text-gray-700 col-span-3 rounded filter-modal-element"
            v-model="filterData.startDate"
        />

        <!--End Date-->
        <label for="filter-end-date" class="text-sm font-medium justify-self-end py-1 my-0.5">End Date:</label>
        <input id="filter-end-date" name="filter-end-date" type="date" class="text-gray-700 col-span-3 rounded filter-modal-element"
               v-model="filterData.endDate"
        />

        <!--account/account-type selector-->
        <div class="col-span-4">
          <account-account-type-toggling-selector
              id="filter-modal"
              class="filter-modal-element"
              v-bind:account-or-account-type-id.sync="filterData.accountOrAccountTypeId"
              v-bind:account-or-account-type-toggled.sync="filterData.accountOrAccountTypeSelected"
          ></account-account-type-toggling-selector>
        </div>

        <!--Tags-->
        <label class="text-sm font-medium justify-self-end py-1 my-0.5">Tags:</label>
        <div class="col-span-3 relative">
          <tags-input-info-loading></tags-input-info-loading>
          <tags-input
              class="filter-modal-element"
              tagsInputName="filter-tags"
              v-bind:existing-tags="tagsStore.list"
              v-bind:selected-tags.sync="filterData.tags"
          ></tags-input>
        </div>

        <!--Income-->
        <label class="text-sm font-medium justify-self-end py-1 my-0.5">Income:</label>
        <div class="col-span-3">
          <toggle-button
              toggle-id="filter-is-income"
              class="filter-toggle-switch filter-modal-element"
              v-bind="toggleButtonProperties"
              v-bind:toggle-state.sync="filterData.isIncome"
              v-on:click.native="flipCompanionSwitch('isExpense')"
          ></toggle-button>
        </div>

        <!--Expense-->
        <label class="text-sm font-medium justify-self-end py-1 my-0.5">Expense:</label>
        <div class="col-span-3">
          <toggle-button
              toggle-id="filter-is-expense"
              class="filter-toggle-switch filter-modal-element"
              v-bind="toggleButtonProperties"
              v-bind:toggle-state.sync="filterData.isExpense"
              v-on:click.native="flipCompanionSwitch('isIncome')"
          ></toggle-button>
        </div>

        <!--Has Attachment(s)-->
        <label class="text-sm font-medium justify-self-end py-1 my-0.5">Has Attachment(s):</label>
        <div class="col-span-3">
          <toggle-button
              toggle-id="filter-has-attachment"
              class="filter-toggle-switch filter-modal-element"
              v-bind="toggleButtonProperties"
              v-bind:toggle-state.sync="filterData.hasAttachment"
              v-on:click.native="flipCompanionSwitch('noAttachment')"
          ></toggle-button>
        </div>

        <!--No Attachment(s)-->
        <label class="text-sm font-medium justify-self-end py-1 my-0.5">No Attachment(s):</label>
        <div class="col-span-3">
          <toggle-button
              toggle-id="filter-no-attachment"
              class="filter-toggle-switch filter-modal-element"
              v-bind="toggleButtonProperties"
              v-bind:toggle-state.sync="filterData.noAttachment"
              v-on:click.native="flipCompanionSwitch('hasAttachment')"
          ></toggle-button>
        </div>

        <!--Transfer-->
        <label class="text-sm font-medium justify-self-end py-1 my-0.5">Transfer:</label>
        <div class="col-span-3">
          <toggle-button
              toggle-id="filter-is-transfer"
              class="filter-toggle-switch filter-modal-element"
              v-bind="toggleButtonProperties"
              v-bind:toggle-state.sync="filterData.isTransfer"
          ></toggle-button>
        </div>

        <!--Unconfirmed-->
        <label class="text-sm font-medium justify-self-end py-1 my-0.5">Not Confirmed:</label>
        <div class="col-span-3">
          <toggle-button
              toggle-id="filter-unconfirmed"
              class="filter-toggle-switch filter-modal-element"
              v-bind="toggleButtonProperties"
              v-bind:toggle-state.sync="filterData.unconfirmed"
          ></toggle-button>
        </div>

        <!--Min Range-->
        <label for="filter-min-value" class="text-sm font-medium justify-self-end py-1 my-0.5">Min Range:</label>
        <div class="col-span-3 relative text-gray-700">
          <input id="filter-min-value" name="filter-min-value" type="text" placeholder="999.99" autocomplete="off" class="placeholder-gray-400 placeholder-opacity-80 rounded w-full pl-6 filter-modal-element"
              v-model="filterData.minValue"
              v-on:change="decimaliseFilterValue('minValue')"
          />
          <span class="currency-symbol absolute left-3 inset-y-2 mt-px text-gray-400 font-medium" v-html="accountCurrencyHtml" ></span>
        </div>

        <!--Max Range-->
        <label for="filter-max-value" class="text-sm font-medium justify-self-end py-1 my-0.5">Max Range:</label>
        <div class="col-span-3 relative text-gray-700">
          <input id="filter-max-value" name="filter-max-value" type="text" placeholder="999.99" autocomplete="off" class="placeholder-gray-400 placeholder-opacity-80 rounded w-full pl-6 filter-modal-element"
              v-model="filterData.maxValue"
              v-on:change="decimaliseFilterValue('maxValue')"
          />
          <span class="currency-symbol absolute left-3 inset-y-2 mt-px text-gray-400 font-medium" v-html="accountCurrencyHtml" ></span>
        </div>
      </section>

      <!-- FOOTER -->
      <footer class="modal-footer py-3 px-0.5 flex justify-between border-t border-gray-200 bg-gray-50">
        <div class="flex">
          <button type="button" id="filter-export-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mx-1 bg-blue-600 text-white opacity-90 hover:opacity-100"
                  v-show="canGenerateFilteredResults"
                  v-on:click="exportData"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
            </svg>
            Export
          </button>
        </div>

        <div class="flex">
          <button type="button" id="filter-cancel-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mx-1 hover:bg-white" v-on:click="closeModal">Cancel</button>

          <button type="button" id="filter-reset-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mx-1 bg-amber-300 opacity-90 hover:opacity-100" v-on:click="resetFields">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reset
          </button>

          <button type="button" id="filter-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 ml-1 bg-blue-600 text-white opacity-90 hover:opacity-100"
              v-on:click="makeFilterRequest"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Filter
          </button>
        </div>
      </footer>
    </div>
  </div>
</template>

<script lang="js">
// utilities
import _ from 'lodash';
import {SnotifyStyle} from "vue-snotify";
import axios from "axios";
// mixins
import {decimaliseInputMixin} from "../../mixins/decimalise-input-mixin";
import {tailwindColorsMixin} from "../../mixins/tailwind-colors-mixin";
// objects
import {Currency} from "../../currency";
// components
import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
import TagsInput from '../tags-input';
import TagsInputInfoLoading from "../tags-input-info-loading.vue";
import ToggleButton from '../toggle-button';
// stores
import {useAccountsStore} from "../../stores/accounts";
import {useAccountTypesStore} from "../../stores/accountTypes";
import {useModalStore} from "../../stores/modal";
import {useTagsStore} from "../../stores/tags";

export default {
  name: "filter-modal",
  mixins: [decimaliseInputMixin, tailwindColorsMixin],
  components: {
    AccountAccountTypeTogglingSelector,
    TagsInput,
    TagsInputInfoLoading,
    ToggleButton,
  },
  data: function(){
    return {
      canShowDisabledAccountAndAccountTypes: false,
      currencyObject: new Currency(),
      filterData: {},
      isVisible: false,
    }
  },
  computed: {
    accountAccountTypeToggleValues(){
      return {
        account: true,
        accountType: false
      }
    },
    accountCurrencyHtml(){
      let account = {};
      if(this.filterData.accountOrAccountTypeSelected === this.accountAccountTypeToggleValues.account){
        account = this.accountsStore.find(this.filterData.accountOrAccountTypeId)
      } else if(this.filterData.accountOrAccountTypeSelected === this.accountAccountTypeToggleValues.accountType){
        let accountType = this.accountTypesStore.find(this.filterData.accountOrAccountTypeId)
        account = this.accountsStore.find(accountType.account_id)
      }

      let currencyCode = _.isEmpty(account) ? '' : account.currency;
      return this.currencyObject.getHtmlFromCode(currencyCode);
    },
    accountsStore(){
      return useAccountsStore();
    },
    accountTypesStore(){
      return useAccountTypesStore();
    },
    defaultData: function(){
      return {
        accountOrAccountTypeSelected: this.accountAccountTypeToggleValues.account,
        accountOrAccountTypeId: "",
        endDate: "",
        hasAttachment: false,
        isExpense: false,
        isIncome: false,
        isTransfer: false,
        maxValue: "",
        minValue: "",
        noAttachment: false,
        startDate: "",
        tags: [],
        unconfirmed: false,
      }
    },
    toggleButtonProperties: function(){
      return {
        colorChecked: this.tailwindColors.blue[600],
        colorUnchecked: this.tailwindColors.gray[400],
        fontSize: 14, // px
        labelChecked: 'Enabled',
        labelUnchecked: 'Disabled',
        height: 40,   // px
        width: 200,   // px
      };
    },
    canGenerateFilteredResults: function(){
      let filterData = _.cloneDeep(this.filterData);
      delete filterData.accountOrAccountTypeSelected;
      delete filterData.tags;
      let defaultData = _.cloneDeep(this.defaultData);
      delete defaultData.accountOrAccountTypeSelected;
      delete defaultData.tags;
      return !_.isEqual(filterData, defaultData)
        || (_.isArray(this.filterData.tags) && !_.isEmpty(this.filterData.tags));
    },
    tagsStore(){
      return useTagsStore();
    }
  },
  methods: {
    closeModal: function(){
      useModalStore().activeModal = useModalStore().MODAL_NONE;
      this.isVisible = false;
    },
    decimaliseFilterValue: function(valueField){
      this.filterData[valueField] = this.decimaliseValue(this.filterData[valueField])
    },
    exportData: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.info, message: "Export Process started"});
      let filterDataParameters = this.processFilterParameters();
      return axios({
        method: 'post',
        url: '/export',
        data: filterDataParameters,
        responseType: 'arraybuffer',
      })
        .then(function(response){
          let filename = response.headers['content-disposition'].split(';').pop().trim().split('=').pop().replaceAll('"', '');

          const url = window.URL.createObjectURL(new Blob([response.data]))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', filename)
          document.body.appendChild(link)
          link.click()

          this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.success, message: "Export Complete"});
          link.remove();
        }.bind(this))
        .catch(function(){
          this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.error, message: "Export Failed"});
        }.bind(this));
    },
    flipCompanionSwitch: function(companionFilter){
      if(this.filterData[companionFilter] === true){
        this.filterData[companionFilter] = false;
      }
    },
    makeFilterRequest: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      this.closeModal();
      let filterDataParameters = this.processFilterParameters();
      this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, {pageNumber: 0, filterParameters: filterDataParameters});
    },
    openModal: function(){
      useModalStore().activeModal = useModalStore().MODAL_FILTER;
      this.isVisible = true;
    },
    processFilterParameters: function(){
      let filterDataParameters = {};

      if(!_.isEmpty(this.filterData.startDate)){
        filterDataParameters.start_date = this.filterData.startDate;
      }
      if(!_.isEmpty(this.filterData.endDate)){
        filterDataParameters.end_date = this.filterData.endDate;
      }

      if(_.isNumber(this.filterData.accountOrAccountTypeId)){
        if(this.filterData.accountOrAccountTypeSelected === this.accountAccountTypeToggleValues.account){
          filterDataParameters.account = this.filterData.accountOrAccountTypeId;
        } else if(this.filterData.accountOrAccountTypeSelected === this.accountAccountTypeToggleValues.accountType){
          filterDataParameters.account_type = this.filterData.accountOrAccountTypeId;
        }
      }

      if(_.isArray(this.filterData.tags) && !_.isEmpty(this.filterData.tags)){
        filterDataParameters.tags = [];
        this.filterData.tags.forEach(function(tag){
          filterDataParameters.tags.push(tag.id);
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
      return filterDataParameters;
    },
    processListOfObjects: function(listOfObjects, canShowDisabled=true){
      if(!canShowDisabled){
        listOfObjects = listOfObjects.filter(function(object){
          return !object.disabled;
        });
      }
      return _.orderBy(listOfObjects, 'name');
    },
    resetFields: function(){
      this.filterData = _.cloneDeep(this.defaultData);
    },
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
/* This ID is obtained from the AccountAccountTypeTogglingSelector component */
::v-deep #account-or-account-type-toggling-selector-for-filter-modal {
  div.flex:last-child {
    /* tailwind class ml-1.5 */
    margin-left: 6px;

    select {
      /* tailwind class w-full */
      width: 100%;
    }
  }
}
</style>