<template>
  <!-- container/background -->
  <div id="transfer-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"
       v-show="isVisible"
  >
    <!-- model content -->
    <div class="modal relative top-20 mx-auto p-2 border w-160 shadow-lg rounded-md bg-white">

      <header class="modal-header border-b border-gray-200 px-2 py-3 flex justify-between bg-gray-50">
        <div class="flex">
          <h3 class="text-xl font-medium flex">
            Transfer
          </h3>
        </div>

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
      <section class="modal-body p-3 grid grid-cols-4 gap-2">
        <!-- date -->
        <label for="transfer-date" class="font-medium justify-self-end py-1">Date:</label>
        <input id="transfer-date" name="transfer-date" type="date" class="text-gray-700 col-span-3 rounded"
               v-model="transferData.date"
        />

        <!-- value -->
        <label for="transfer-value" class="font-medium justify-self-end py-1">Value:</label>
        <div class="col-span-3 relative text-gray-700">
          <span class="absolute left-3 inset-y-2 mt-px" v-html="accountTypeMeta.currencyHtml"></span>
          <input id="transfer-value" name="transfer-value" type="text" placeholder="999.99" autocomplete="off" class="placeholder-gray-400 placeholder-opacity-80 rounded w-full"
                 v-model="transferData.value"
                 v-on:change="transferData.value = decimaliseValue(transferData.value)"
          />
        </div>

        <!-- account-type (from) -->
        <label for="from-account-type" class="font-medium justify-self-end py-1">From:</label>
        <div class="col-span-3 relative text-gray-700">
          <span class="loading absolute inset-y-3 left-2" v-show="!areAccountTypesAvailable">
            <svg class="animate-spin mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>

          <select name="from-account-type" id="from-account-type" class="rounded w-full"
                  v-model="transferData.from_account_type_id"
                  v-on:change="updateAccountTypeMeta('from')"
          >
            <option></option>
            <option
                v-for="accountType in listAccountTypes"
                v-bind:key="accountType.id"
                v-bind:value="accountType.id"
                v-text="accountType.name"
                v-show="!accountType.disabled"
            ></option>
            <option v-bind:value="accountTypeMeta.externalAccountTypeId">[External account]</option>
          </select>

          <div id="transfer-from-account-type-meta" class="text-xs pt-1"
               v-bind:class="{'hidden': !canShowFromAccountTypeMeta, 'text-blue-500': isAccountFromEnabled, 'text-gray-400': !isAccountFromEnabled}"
          >
            <p>
              <span class="has-text-weight-semibold has-padding-right">Account Name:</span>
              <span id="from-account-type-meta-account-name" v-text="accountTypeMeta.from.accountName"></span>
            </p>
            <p>
              <span class="has-text-weight-semibold has-padding-right">Last 4 Digits:</span>
              <span id="from-account-type-meta-last-digits" v-text="accountTypeMeta.from.lastDigits"></span>
            </p>
          </div>
        </div>

        <!-- account-type (to) -->
        <label for="to-account-type" class="font-medium justify-self-end py-1">To:</label>
        <div class="col-span-3 relative text-gray-700">
          <span class="loading absolute inset-y-3 left-2" v-show="!areAccountTypesAvailable">
            <svg class="animate-spin mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>

          <select id="to-account-type" name="to-account-type" class="rounded w-full"
                  v-model="transferData.to_account_type_id"
                  v-on:change="updateAccountTypeMeta('to')"
          >
            <option></option>
            <option
                v-for="accountType in listAccountTypes"
                v-bind:key="accountType.id"
                v-bind:value="accountType.id"
                v-text="accountType.name"
                v-show="!accountType.disabled"
            ></option>
            <option v-bind:value="accountTypeMeta.externalAccountTypeId">[External account]</option>
          </select>

          <div id="transfer-to-account-type-meta" class="text-xs pt-1"
               v-bind:class="{'hidden': !canShowToAccountTypeMeta, 'text-blue-500': isAccountToEnabled, 'text-gray-400': !isAccountToEnabled}"
          >
            <p>
              <span class="font-semibold pr-0.5">Account Name:</span>
              <span id="to-account-type-meta-account-name" v-text="accountTypeMeta.to.accountName"></span>
            </p>
            <p>
              <span class="font-semibold pr-0.5">Last 4 Digits:</span>
              <span id="to-account-type-meta-last-digits" v-text="accountTypeMeta.to.lastDigits"></span>
            </p>
          </div>
        </div>

        <!-- memo -->
        <label for="transfer-memo" class="font-medium justify-self-end py-1">Memo:</label>
        <textarea id="transfer-memo" name="transfer-memo" class="text-gray-700 col-span-3 rounded h-32"
                  v-model="transferData.memo"
        ></textarea>

        <!-- tags -->
        <label class="font-medium justify-self-end py-1">Tags:</label>
        <div class="col-span-3 relative">
          <span class="loading absolute inset-y-2 right-0 z-10" v-show="!areTagsSet">
            <svg class="animate-spin mr-3 h-5 w-5 text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
          <tags-input
              tagsInputName="transfer-tags"
              v-bind:existingTags="listTags"
              v-bind:selected-tags.sync="transferData.tags"
          ></tags-input>
        </div>

        <!-- attachment upload -->
        <div class="col-span-4">
          <file-drag-n-drop id="transfer-modal" v-bind:attachments.sync="transferData.attachments"></file-drag-n-drop>
        </div>
      </section>

      <!-- FOOTER -->
      <footer class="modal-footer py-3 px-0.5 flex justify-end border-t border-gray-200 bg-gray-50">
        <button type="button" id="transfer-cancel-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mx-1 hover:bg-white" v-on:click="closeModal">Cancel</button>

        <button type="button" id="transfer-save-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 ml-1 text-white bg-green-500 disabled:opacity-50"
                v-on:click="saveTransfer"
                v-bind:disabled="!canSave"
                v-bind:class="{'opacity-65 cursor-not-allowed': !canSave, 'opacity-90 hover:opacity-100': canSave}"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px mr-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
          Save
        </button>
      </footer>
    </div>
  </div>
</template>

<script lang="js">
// utilities
import _ from 'lodash';
import Store from '../../store';
// mixins
import {accountTypesObjectMixin} from "../../mixins/account-types-object-mixin";
import {decimaliseInputMixin} from "../../mixins/decimalise-input-mixin";
import {tagsObjectMixin} from "../../mixins/tags-object-mixin";
// objects
import {Entry} from "../../entry";
// components
import TagsInput from "../tags-input";
import FileDragNDrop from "../file-drag-n-drop";

export default {
  name: "transfer-modal",
  mixins: [accountTypesObjectMixin, decimaliseInputMixin, tagsObjectMixin],
  components: {
    FileDragNDrop,
    TagsInput,
  },
  data: function(){
    return {
      entryObject: new Entry(),

      accountTypeMeta: {
        from: {
          accountName: "",
          lastDigits: "",
          isEnabled: true
        },
        to: {
          accountName: "",
          lastDigits: "",
          isEnabled: true
        },
        externalAccountTypeId: 0
      },

      isVisible: false,

      transferData: {}, // this gets filled with values from defaultData
    }
  },
  computed: {
    accountTypeMetaDefaults: function(){
      return {
        accountName: "",
        lastDigits: "",
        isEnabled: true
      }
    },
    areAccountTypesSet: function(){
      return this.listAccountTypes.length > 0;
    },
    canSave: function(){
      if(isNaN(Date.parse(this.transferData.date))){
        return false;
      }
      let transferValue = _.toNumber(this.transferData.value);
      if(this.transferData.value === "" || isNaN(transferValue) || !_.isNumber(transferValue)){
        return false;
      }
      if(!this.hasValidFromAccountTypeBeenSelected){
        return false;
      }
      if(!this.hasValidToAccountTypeBeenSelected){
        return false;
      }
      if(this.transferData.from_account_type_id === this.transferData.to_account_type_id){
        return false;
      }
      if(_.isEmpty(this.transferData.memo)){
        return false;
      }

      return true;
    },
    canShowFromAccountTypeMeta: function(){
      return this.canShowAccountTypeMeta(this.transferData.from_account_type_id);
    },
    canShowToAccountTypeMeta: function(){
      return this.canShowAccountTypeMeta(this.transferData.to_account_type_id);
    },
    currentDate: function(){
      let today = new Date();
      return today.getFullYear()+'-'
          +(today.getMonth()<9?'0':'')+(today.getMonth()+1)+'-'	// months in JavaScript start from 0=January
          +(today.getDate()<10?'0':'')+today.getDate();
    },
    currentPage: function(){
      return Store.getters.currentPage;
    },
    defaultData: function(){
      return {
        attachments: [],
        date: this.currentDate,
        from_account_type_id: "",
        memo: "",
        tags: [],
        to_account_type_id: "",
        value: "",
      }
    },
    hasValidFromAccountTypeBeenSelected: function(){
      return this.hasValidAccountTypeBeenSelected(this.transferData.from_account_type_id);
    },
    hasValidToAccountTypeBeenSelected: function(){
      return this.hasValidAccountTypeBeenSelected(this.transferData.to_account_type_id);
    },
    isAccountFromEnabled: function(){
      return this.accountTypeMeta.from.isEnabled;
    },
    isAccountToEnabled: function(){
      return this.accountTypeMeta.to.isEnabled;
    },
    listAccountTypes: function(){
      return _.orderBy(this.rawAccountTypesData, 'name');
    },
  },
  methods: {
    canShowAccountTypeMeta: function(accountTypeId){
      accountTypeId = parseInt(accountTypeId);
      return this.hasValidAccountTypeBeenSelected(accountTypeId) && accountTypeId !== this.accountTypeMeta.externalAccountTypeId;
    },
    closeModal: function(){
      this.setModalState(Store.getters.STORE_MODAL_NONE);
      this.isVisible = false;
      this.resetData();
      this.updateAccountTypeMeta('from');
      this.updateAccountTypeMeta('to');
    },
    hasValidAccountTypeBeenSelected: function(accountTypeId){
      accountTypeId = parseInt(accountTypeId);
      return !isNaN(accountTypeId) || accountTypeId === this.accountTypeMeta.externalAccountTypeId;
    },
    openModal: function(){
      this.setModalState(Store.getters.STORE_MODAL_TRANSFER);
      this.isVisible = true;
      this.resetData();
      this.updateAccountTypeMeta('from');
      this.updateAccountTypeMeta('to');
    },
    saveTransfer: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

      let transferData = {};
      if(!isNaN(Date.parse(this.transferData.date))){
        transferData.entry_date = this.transferData.date;
      }
      let transferValue = _.toNumber(this.transferData.value);
      if(this.transferData.value !== "" && !isNaN(transferValue) && _.isNumber(transferValue)){
        transferData.entry_value = transferValue;
      }
      if(this.hasValidFromAccountTypeBeenSelected){
        transferData.from_account_type_id = this.transferData.from_account_type_id;
      }
      if(this.hasValidToAccountTypeBeenSelected){
        transferData.to_account_type_id = this.transferData.to_account_type_id;
      }
      if(!_.isEmpty(this.transferData.memo)){
        transferData.memo = this.transferData.memo;
      }
      // tags
      if(_.isArray(this.transferData.tags)){
        transferData.tags = [];
        this.transferData.tags.forEach(function(tag){
          transferData.tags.push(tag.id);
        });
      }
      // attachments
      if(_.isArray(this.transferData.attachments)){
        transferData.attachments = [];
        this.transferData.attachments.forEach(function(attachment){
          if(
              // each "attachment" MUST be an array
              (_.isArray(attachment) || _.isObject(attachment))
              // each "attachment" MUST have a "uuid"
              && (!_.isEmpty(attachment.uuid) && _.isString(attachment.uuid))
              // each "attachment" MUST have a "name"
              && (!_.isEmpty(attachment.name) && _.isString(attachment.name))
          ){
            transferData.attachments.push(attachment);
          }
        });
      }

      this.entryObject.saveTransfer(transferData).then(function(notification){
        if(!_.isEmpty(notification)){
          this.$eventHub.broadcast(
              this.$eventHub.EVENT_NOTIFICATION,
              {type: notification.type, message: notification.message}
          );
        }
        this.$eventHub.broadcast(this.$eventHub.EVENT_ACCOUNT_UPDATE);
        this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, this.currentPage);
        this.closeModal();
      }.bind(this));
    },
    setModalState: function(modal){
      Store.dispatch('currentModal', modal);
    },
    resetData: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_FILE_DROP_UPDATE, {modal: 'transfer-modal', task: 'enable'});
      this.$eventHub.broadcast(this.$eventHub.EVENT_FILE_DROP_UPDATE, {modal: 'transfer-modal', task: 'clear'});
      this.transferData = _.cloneDeep(this.defaultData);
      this.accountTypeMeta.from = _.cloneDeep(this.accountTypeMetaDefaults);
      this.accountTypeMeta.to = _.cloneDeep(this.accountTypeMetaDefaults);
    },
    updateAccountTypeMeta: function(accountTypeSelect){
      let account = this.accountTypesObject.getAccount(this.transferData[accountTypeSelect+'_account_type_id']);
      this.accountTypeMeta[accountTypeSelect].accountName = account.name;
      let accountType = this.accountTypesObject.find(this.transferData[accountTypeSelect+'_account_type_id']);
      this.accountTypeMeta[accountTypeSelect].lastDigits = accountType.last_digits;
      this.accountTypeMeta[accountTypeSelect].isEnabled = !account.disabled && !accountType.disabled;
    },
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_TRANSFER_MODAL_OPEN, this.openModal);
    this.$eventHub.listen(this.$eventHub.EVENT_TRANSFER_MODAL_CLOSE, this.closeModal);
  },
  mounted: function(){
    this.resetData();
  }
}
</script>

<style lang="scss" scoped>
</style>