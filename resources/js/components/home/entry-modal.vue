<template>
  <!-- container/background -->
  <div id="entry-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"
      v-show="isVisible"
  >
    <!--modal content-->
    <div class="modal relative top-20 mx-auto p-2 border w-160 shadow-lg rounded-md bg-white">
      <header class="modal-header border-b border-gray-200 px-2 py-3 flex justify-between bg-gray-50">
        <div class="flex">
          <p class="text-xl font-medium flex">
            <span>Entry: <span v-if="entryData.id" v-text="entryData.id"></span><span v-else>new</span></span>
            <input type="hidden" name="entry-id" id="entry-id" v-model="entryData.id" />
          </p>

          <button type="button" id="entry-transfer-btn" class="justify-center rounded-md border border-gray-300 px-2 py-1.5 bg-white text-gray-700 hover:bg-gray-50 ml-5"
              v-if="isTransfer"
              v-bind:disabled="isExternalTransfer"
              v-on:click="primeDataForModal(entryData.transfer_entry_id)"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </button>
        </div>

        <div class="flex">
          <label for="entry-confirm" class="rounded-md py-0.5 px-2 inline-flex"
                 v-bind:class="{
                  'bg-white hover:bg-gray-100 text-gray-400': !entryData.confirm,
                  'bg-green-400 bg-opacity-90 text-white hover:bg-opacity-100': entryData.confirm,
                  'cursor-pointer': !isLocked,
                  'cursor-not-allowed': isLocked
                }"
          >
            <input type="checkbox" id="entry-confirm" name="entry-confirm" class="appearance-none hidden"
                   v-model="entryData.confirm"
                   v-bind:disabled="isLocked"
            />
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5 mr-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Confirmed
          </label>

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
        <label for="entry-date" class="font-medium justify-self-end py-1">Date:</label>
        <input id="entry-date" name="entry-date" type="date" class="text-gray-700 col-span-3 rounded"
               v-model="entryData.entry_date"
               v-bind:readonly="isLocked"
        />

        <!-- value -->
        <label for="entry-value" class="font-medium justify-self-end py-1">Value:</label>
        <div class="col-span-3 relative text-gray-700">
          <input id="entry-value" name="entry-value" type="text" placeholder="999.99" autocomplete="off" class="placeholder-gray-400 placeholder-opacity-80 rounded w-full pl-6"
                 v-model="entryData.entry_value"
                 v-bind:readonly="isLocked"
                 v-on:change="entryData.entry_value = decimaliseValue(entryData.entry_value)"
          />
          <span class="currency-symbol absolute left-3 inset-y-2 mt-px text-gray-400 font-medium" v-html="accountTypeMeta.currencyHtml"></span>
        </div>

        <!-- account-type -->
        <label for="entry-account-type" class="font-medium justify-self-end py-1">Account Type:</label>
        <div class="col-span-3 relative text-gray-700">
          <span class="loading absolute inset-y-3 left-2" v-show="!accountTypesStore.isSet">
            <svg class="animate-spin mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>

          <select id="entry-account-type" name="entry-account-type" class="rounded w-full"
                  v-model="entryData.account_type_id"
                  v-on:change="updateAccountTypeMeta"
                  v-bind:disabled="isLocked"
          >
            <option></option>
            <option
                v-for="accountType in accountTypesStore.list"
                v-bind:key="accountType.id"
                v-bind:value="accountType.id"
                v-text="accountType.name"
                v-show="accountType.active"
            ></option>
          </select>

          <div id="entry-account-type-meta" class="text-xs pt-1"
               v-bind:class="{'hidden': !hasAccountTypeBeenSelected, 'text-blue-500': isAccountEnabled, 'text-gray-400': !isAccountEnabled}"
          >
            <p>
              <span class="has-text-weight-semibold has-padding-right">Account Name:</span>
              <span id="entry-account-type-meta-account-name" v-text="accountTypeMeta.accountName"></span>
            </p>
            <p>
              <span class="has-text-weight-semibold has-padding-right">Last 4 Digits:</span>
              <span class="entry-account-type-meta-last-digits" v-text="accountTypeMeta.lastDigits"></span>
            </p>
          </div>
        </div>

        <!-- memo -->
        <label for="entry-memo" class="font-medium justify-self-end py-1">Memo:</label>
        <textarea id="entry-memo" class="text-gray-700 col-span-3 rounded h-32"
                  v-model="entryData.memo"
                  v-bind:readonly="isLocked"
        ></textarea>

        <!-- income/expense toggle -->
        <div class="col-span-4 justify-self-center">
          <toggle-button
              v-bind="toggleButtonProperties"
              v-bind:toggle-state.sync="entryData.expense"
          ></toggle-button>
        </div>

        <!-- tags -->
        <label class="font-medium justify-self-end py-1">Tags:</label>
        <div class="col-span-3 relative">
          <span class="loading absolute inset-y-2 right-0 z-10" v-show="!tagsStore.isSet">
            <svg class="animate-spin mr-3 h-5 w-5 text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
          <tags-input
              v-show="!isLocked"
              tagsInputName="entry-tags"
              v-bind:existingTags="tagsStore.list"
              v-bind:selected-tags.sync="entryData.tags"
          ></tags-input>

          <div v-show="isLocked" id="entry-tags-locked" class="tags flex flex-wrap shadow border-t rounded py-1 px-2 min-h-full">
            <span class="tag rounded-full bg-gray-200 text-gray-800 text-xs px-2 py-1 mx-1 my-0.5"
                  v-text="tag"
                  v-for="tag in displayReadOnlyTags"
                  v-bind:key="tag"
            ></span>
          </div>
        </div>

        <!-- attachment upload -->
        <div class="col-span-4" v-show="!isLocked">
          <file-drag-n-drop id="entry-modal" v-bind:attachments.sync="entryData.attachments"></file-drag-n-drop>
        </div>

        <!-- existing attachments -->
        <div id="existing-entry-attachments" class="col-span-4 justify-self-center w-full">
          <entry-modal-attachment
              v-for="entryAttachment in orderedAttachments"
              v-show="!entryAttachment.tmp_filename"
              v-bind:key="entryAttachment.uuid"
              v-bind:uuid="entryAttachment.uuid"
              v-bind:name="entryAttachment.name"
              v-bind:entryId="entryData.id"
              v-bind:isLocked="isLocked"
          ></entry-modal-attachment>
        </div>
      </section>

      <!-- FOOTER -->
      <footer class="modal-footer py-3 px-0.5 flex justify-between border-t border-gray-200 bg-gray-50">
        <div class="flex">
          <button type="button" id="entry-delete-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 bg-red-600 text-white opacity-95 hover:opacity-100"
                  v-show=isDeletable
                  v-on:click="deleteEntry"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
          </button>
        </div>

        <div class="flex">
          <button type="button" id="entry-lock-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mr-1 hover:bg-white"
                  v-show="isConfirmed"
                  v-on:click="toggleLockState"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="lock-icon h-5 w-5" viewBox="0 0 20 20" fill="currentColor" v-show="isLocked">
              <!-- LOCKED -->
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>

            <svg xmlns="http://www.w3.org/2000/svg" class="unlock-icon h-5 w-5" viewBox="0 0 20 20" fill="currentColor" v-show="!isLocked">
              <!-- UNLOCKED -->
              <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z" />
            </svg>
          </button>
          <button type="button" id="entry-cancel-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 mx-1 hover:bg-white" v-on:click="closeModal">Cancel</button>
          <button type="button" id="entry-save-btn" class="inline-flex justify-center rounded-md border border-gray-300 px-3 py-2 ml-1 text-white bg-green-500 disabled:opacity-50"
                  v-show="!isLocked"
                  v-on:click="saveEntry"
                  v-bind:disabled="!canSave"
                  v-bind:class="{'opacity-65 cursor-not-allowed': !canSave, 'opacity-90 hover:opacity-100': canSave}"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-px mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Save
          </button>
        </div>
      </footer>
    </div>
</div>
</template>

<script lang="js">
// utilities
import _ from 'lodash';
// objects
import {Currency} from '../../currency';
import {Entry} from "../../entry";
// mixins
import {decimaliseInputMixin} from "../../mixins/decimalise-input-mixin";
import {tailwindColorsMixin} from "../../mixins/tailwind-colors-mixin";
// components
import FileDragNDrop from "./../file-drag-n-drop";
import EntryModalAttachment from "./entry-modal-attachment";
import ToggleButton from './../toggle-button';
import TagsInput from "./../tags-input";
// stores
import {useAccountsStore} from "../../stores/accounts";
import {useAccountTypesStore} from "../../stores/accountTypes";
import {useEntriesStore} from "../../stores/entries";
import {useModalStore} from "../../stores/modal";
import {usePaginationStore} from "../../stores/pagination";
import {useTagsStore} from "../../stores/tags";

export default {
  name: "entry-modal",
  mixins: [decimaliseInputMixin, tailwindColorsMixin],
  components: {
    EntryModalAttachment,
    FileDragNDrop,
    TagsInput,
    ToggleButton,
  },
  data: function(){
    return {
      accountTypeMeta: {}, // this gets filled with values from defaultAccountTypMeta

      entryData: {}, // this gets filled with values from defaultEntryData

      isDeletable: false,
      isLocked: false,
      isVisible: false,
    }
  },
  computed: {
    accountsStore: function(){
      return useAccountsStore()
    },
    accountTypesStore: function(){
      return useAccountTypesStore()
    },
    canSave: function(){
      if(isNaN(Date.parse(this.entryData.entry_date))){
        return false;
      }
      let entryValue = _.toNumber(this.entryData.entry_value);
      if(this.entryData.entry_value === "" || isNaN(entryValue) || !_.isNumber(entryValue)){
        return false;
      }
      if(!_.isNumber(this.entryData.account_type_id)){
        return false;
      }
      if(_.isEmpty(this.entryData.memo)){
        return false;
      }
      if(!_.isBoolean(this.entryData.expense)){
        return false;
      }

      return true;
    },
    currentDate: function(){
      let today = new Date();
      return today.getFullYear()+'-'
        +(today.getMonth()<9?'0':'')+(today.getMonth()+1)+'-'	// months in JavaScript start from 0=January
        +(today.getDate()<10?'0':'')+today.getDate();
    },
    currencyObject: function(){
      return new Currency()
    },
    defaultAccountTypeMeta: function(){
      return {
        accountName: "",
        currencyHtml: "&#36;",
        lastDigits: "",
        isEnabled: true
      }
    },
    defaultEntryData: function(){
      return {
        id: null,
        entry_date: this.currentDate,
        account_type_id: "",
        entry_value: "",
        memo: "",
        expense: true,
        confirm: false,
        transfer_entry_id: null,
        tags: [],
        attachments: []
      }
    },
    displayReadOnlyTags: function(){
      let currentTags = typeof this.entryData.tags == 'undefined' ? [] : this.entryData.tags;
      return currentTags.map(function(tag){ return tag.name; });
    },
    entriesStore: function(){
      return useEntriesStore();
    },
    entryObject: function(){
      return new Entry();
    },
    hasAccountTypeBeenSelected: function(){
      return this.entryData.account_type_id !== '';
    },
    isAccountEnabled: function(){
      return this.accountTypeMeta.isEnabled;
    },
    isConfirmed: function(){
      return this.entryData.confirm && this.entryData.id;
    },
    isExternalTransfer: function(){
      return _.isEqual(this.entryData.transfer_entry_id, 0);
    },
    isTransfer: function(){
      return _.isNumber(this.entryData.transfer_entry_id);
    },
    orderedAttachments: function(){
      return _.orderBy(this.entryData.attachments, 'name');
    },
    tagsStore: function(){
      return useTagsStore()
    },
    toggleButtonProperties: function(){
      return {
        colorChecked: this.tailwindColors.yellow[400],
        colorUnchecked: this.tailwindColors.teal[500],
        disabled: this.isLocked,
        fontSize: 20, // px
        labelChecked: 'Expense',
        labelUnchecked: 'Income',
        height: 40, // px
        toggleId: "entry-expense",
        width: 200, // px
      };
    },
  },
  methods: {
    broadcastUpdateRequestForAccountsColumnAndEntriesTable: function(){
      // allow accounts data to be once again fetched
      this.$eventHub.broadcast(this.$eventHub.EVENT_ACCOUNT_UPDATE);
      // update entries table
      this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, usePaginationStore().currentPage);
      // don't need to broadcast an event to hide the loading modal here
      // already taken care of at the end of the entry-table update event process
    },
    closeModal: function(){
      useModalStore().activeModal = useModalStore().MODAL_NONE
      this.isDeletable = false;
      this.isVisible = false;
      this.resetEntryData();
      this.unlockModal();
    },
    deleteEntry: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      this.entryObject
        .delete(this.entryData.id)
        .then(function(deleteResult){
          if(!_.isEmpty(deleteResult.notification)){
            this.$eventHub.broadcast(
              this.$eventHub.EVENT_NOTIFICATION,
              {type: deleteResult.notification.type, message: deleteResult.notification.message.replace('%s', this.entryData.id)}
            );
          }
          this.closeModal();
          if(deleteResult.deleted){
            this.broadcastUpdateRequestForAccountsColumnAndEntriesTable();
          } else {
            this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
          }
        }.bind(this));
    },
    lockModal: function(){
      this.isLocked = true;
      this.$eventHub.broadcast(this.$eventHub.EVENT_FILE_DROP_UPDATE, {modal: 'entry-modal', task: 'disable'});
    },
    openModal: function(entryData = {}){
      useModalStore().activeModal = useModalStore().MODAL_ENTRY;
      if(!_.isEmpty(entryData)){
        this.entryData = _.clone(entryData);
        this.entryData.confirm ? this.lockModal() : this.unlockModal();
        this.isDeletable = true;
      } else {
        this.resetEntryData();
        this.isDeletable = false;
      }
      this.isVisible = true;
      this.updateAccountTypeMeta();
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
    },
    primeDataForModal: function(entryId = null){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      if(!_.isEmpty(entryId) || _.isNumber(entryId)){ // isNumber is used to handle isEmpty() reading numbers as empty
        if(_.isObject(entryId)){
          // entryId was passed as part of an event payload
          entryId = entryId[0];
        }
        // new Promise(function(resolve, reject){
        //   let entryData = this.entriesStore.find(entryId);
        //   if(this.entryObject.isDataUpToDate(entryData)){
        //     resolve(entryData);
        //   } else {
        //     reject(entryId);
        //   }
        // }.bind(this))
        //   .then(this.openModal)       // resolve
        //   .catch(function(entryId){   // reject
        this.entryObject.fetch(entryId)
          .then(function(fetchResult){
            let freshlyFetchedEntryData = {};
            if(fetchResult.fetched){
              freshlyFetchedEntryData = this.entriesStore.find(entryId);
            }
            this.openModal(freshlyFetchedEntryData);
            if(!_.isEmpty(fetchResult.notification)){
              this.$eventHub.broadcast(
                this.$eventHub.EVENT_NOTIFICATION,
                {type: fetchResult.notification.type, message: fetchResult.notification.message}
              );
            }
          }.bind(this));
        // }.bind(this));
      } else {
        this.openModal({});
      }
    },
    resetEntryData: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_FILE_DROP_UPDATE, {modal: 'entry-modal', task: 'clear'});
      this.entryData = _.cloneDeep(this.defaultEntryData);
      this.accountTypeMeta = _.cloneDeep(this.defaultAccountTypeMeta);
      this.unlockModal();
    },
    saveEntry: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      // validate inputs
      let newEntryData = {};
      // id
      if(_.isNumber(this.entryData.id) || _.isNull(this.entryData.id)){
        newEntryData.id = this.entryData.id;
      }
      // confirm
      if(_.isBoolean(this.entryData.confirm)){
        newEntryData.confirm = this.entryData.confirm;
      }
      // entry_date
      if(!isNaN(Date.parse(this.entryData.entry_date))){
        newEntryData.entry_date = this.entryData.entry_date;
      }
      // entry_value
      let entryValue = _.toNumber(this.entryData.entry_value);
      if(this.entryData.entry_value !== "" || !isNaN(entryValue) || _.isNumber(entryValue)){
        newEntryData.entry_value = entryValue;
      }
      // account_type_id
      if(_.isNumber(this.entryData.account_type_id)){
        newEntryData.account_type_id = this.entryData.account_type_id;
      }
      // memo
      if(!_.isEmpty(this.entryData.memo)){
        newEntryData.memo = this.entryData.memo;
      }
      // expense
      if(_.isBoolean(this.entryData.expense)){
        newEntryData.expense = this.entryData.expense;
      }
      // tags
      if(_.isArray(this.entryData.tags)){
        newEntryData.tags = [];
        this.entryData.tags.forEach(function(tag){
          newEntryData.tags.push(tag.id);
        });
      }
      // attachments
      if(_.isArray(this.entryData.attachments)){
        newEntryData.attachments = [];
        this.entryData.attachments.forEach(function(attachment){
          if(
            // each "attachment" MUST be an array
            (_.isArray(attachment) || _.isObject(attachment))
            // each "attachment" MUST have a "uuid"
            && (!_.isEmpty(attachment.uuid) && _.isString(attachment.uuid))
            // each "attachment" MUST have a "name"
            && (!_.isEmpty(attachment.name) && _.isString(attachment.name))
          ){
            newEntryData.attachments.push(attachment);
          }
        });
      }
      this.entryObject.save(newEntryData)
        .then(function(notification){
          // show a notification if needed
          if(!_.isEmpty(notification)){
            this.$eventHub.broadcast(
              this.$eventHub.EVENT_NOTIFICATION,
              {type: notification.type, message: notification.message.replace('%s', this.entryData.id)}
            );
          }
          this.broadcastUpdateRequestForAccountsColumnAndEntriesTable();
        }.bind(this))
        .finally(this.closeModal.bind(this));
    },
    toggleLockState: function(){
      if(this.isLocked){
        this.unlockModal();
      } else {
        this.entryData = _.clone(this.entriesStore.find(this.entryData.id));
        this.lockModal();
      }
    },
    unlockModal: function(){
      this.isLocked = false;
      this.$eventHub.broadcast(this.$eventHub.EVENT_FILE_DROP_UPDATE, {modal: 'entry-modal', task: 'enable'});
      this.updateAccountTypeMeta();
    },
    updateAccountTypeMeta: function(){
      let accountType = this.accountTypesStore.find(this.entryData.account_type_id);
      let account = this.accountsStore.find(accountType.account_id)
      this.accountTypeMeta.accountName = account.name;
      this.accountTypeMeta.lastDigits = accountType.last_digits;
      this.accountTypeMeta.isEnabled = accountType.active && account.active;

      this.accountTypeMeta.currencyHtml = this.currencyObject.getHtmlFromCode(account.currency);
    },
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_ENTRY_MODAL_OPEN, this.primeDataForModal);
    this.$eventHub.listen(this.$eventHub.EVENT_ENTRY_MODAL_UPDATE_DATA, this.openModal);
    this.$eventHub.listen(this.$eventHub.EVENT_ENTRY_MODAL_CLOSE, this.closeModal);
  },
  mounted: function(){
    this.resetEntryData();
  }
}
</script>

<style lang="scss" scoped>
</style>