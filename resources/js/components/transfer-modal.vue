<template>
    <div id="transfer-modal" class="modal" v-bind:class="{'is-active': isVisible}">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Transfer</p>

                <button class="delete" aria-label="close" v-on:click="closeModal"></button>
            </header>

            <section class="modal-card-body">
                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="transfer-date">Date:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <input class="input has-text-grey-dark" id="transfer-date" name="transfer-date" type="date"
                            v-model="transferData.date"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="transfer-value">Value:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <input class="input has-text-grey-dark" id="transfer-value" name="transfer-value" type="text" placeholder="999.99" autocomplete="off"
                           v-model="transferData.value"
                           v-on:change="decimaliseValue"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="from-account-type">From:</label></div>
                    <div class="field-body"><div class="field">
                        <div class="control"><div class="select" v-bind:class="{'is-loading': !areAccountTypesSet}">
                            <select name="from-account-type" id="from-account-type" class="has-text-grey-dark transfer-account-type"
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
                        </div></div>
                        <div id="transfer-from-account-type-meta"  class="help" v-bind:class="{'is-hidden': !canShowFromAccountTypeMeta, 'has-text-info': isAccountFromEnabled, 'has-text-grey-light': !isAccountFromEnabled}">
                            <p>
                                <span class="has-text-weight-semibold has-padding-right">Account Name:</span>
                                <span id="from-account-type-meta-account-name" v-text="accountTypeMeta.from.accountName"></span>
                            </p>
                            <p>
                                <span class="has-text-weight-semibold has-padding-right">Last 4 Digits:</span>
                                <span id="from-account-type-meta-last-digits" v-text="accountTypeMeta.from.lastDigits"></span>
                            </p>
                        </div>
                    </div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="to-account-type">To:</label></div>
                    <div class="field-body"><div class="field">
                        <div class="control"><div class="select" v-bind:class="{'is-loading': !areAccountTypesSet}">
                            <select name="to-account-type" id="to-account-type" class="has-text-grey-dark transfer-account-type"
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
                        </div></div>
                        <div id="transfer-to-account-type-meta" class="help" v-bind:class="{'is-hidden': !canShowToAccountTypeMeta, 'has-text-info': isAccountToEnabled, 'has-text-grey-light': !isAccountToEnabled}">
                            <p>
                                <span class="has-text-weight-semibold has-padding-right">Account Name:</span>
                                <span id="to-account-type-meta-account-name" v-text="accountTypeMeta.to.accountName"></span>
                            </p>
                            <p>
                                <span class="has-text-weight-semibold has-padding-right">Last 4 Digits:</span>
                                <span id="to-account-type-meta-last-digits" v-text="accountTypeMeta.to.lastDigits"></span>
                            </p>
                        </div>
                    </div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="transfer-memo">Memo:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <textarea id="transfer-memo" name="transfer-memo" class="textarea has-text-grey-dark"
                            v-model="transferData.memo"
                        ></textarea>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label">Tags:</label></div>
                    <div class="field-body"><div class="field"><div class="control" v-bind:class="{'is-loading': !areTagsSet}">
                      <TagsInput
                        v-model:tags-input="transferData.tags"
                        v-bind:existing-tags="listTags"
                        v-bind:selected-tags="transferData.tags"
                      ></TagsInput>
                    </div></div></div>
                </div>

                <div class="field"><div class="control">
                  <file-drag-n-drop id="transfer-modal" v-model:attachments="transferData.attachments"></file-drag-n-drop>
                </div></div>
            </section>

            <footer class="modal-card-foot">
                <div class="container">
                    <div class="field is-grouped">
                        <div class="control is-expanded"></div>
                        <div class="control">
                            <button type="button" id="transfer-cancel-btn" class="button" v-on:click="closeModal">Cancel</button>
                            <button type="button" id="transfer-save-btn" class="button is-success"
                                v-on:click="saveTransfer"
                                v-bind:disabled="!canSave"
                                >
                                <i class="fas fa-check has-padding-right"></i> Save
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
  import {accountTypesObjectMixin} from "../mixins/account-types-object-mixin";
  import {Entry} from "../entry";
  import {tagsObjectMixin} from "../mixins/tags-object-mixin";
  // components
  import TagsInput from "./tags-input";
  import FileDragNDrop from "./file-drag-n-drop";

    export default {
        name: "transfer-modal",
        mixins: [accountTypesObjectMixin, tagsObjectMixin],
        components: {
          FileDragNDrop,
          TagsInput
        },
        data: function(){
            return {
                entryObject: new Entry(),

                accountTypeMeta: {
                    default: {
                        accountName: "",
                        lastDigits: "",
                        isEnabled: true
                    },
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

                defaultData: {
                    date: "",
                    value: "",
                    memo: "",
                    from_account_type_id: "",
                    to_account_type_id: "",
                    tags: [],
                    attachments: []
                },
            }
        },
        computed: {
            areAccountTypesSet: function(){
                return this.listAccountTypes.length > 0;
            },
            canShowFromAccountTypeMeta: function(){
                return this.canShowAccountTypeMeta(this.transferData.from_account_type_id);
            },
            canShowToAccountTypeMeta: function(){
                return this.canShowAccountTypeMeta(this.transferData.to_account_type_id);
            },
            isAccountToEnabled: function(){
                return this.accountTypeMeta.to.isEnabled;
            },
            isAccountFromEnabled: function(){
                return this.accountTypeMeta.from.isEnabled;
            },
            currentPage: function(){
                return store.getters.currentPage;
            },
            currentDate: function(){
                let today = new Date();
                return today.getFullYear()+'-'
                    +(today.getMonth()<9?'0':'')+(today.getMonth()+1)+'-'	// months in JavaScript start from 0=January
                    +(today.getDate()<10?'0':'')+today.getDate();
            },
            hasValidFromAccountTypeBeenSelected: function(){
                return this.hasValidAccountTypeBeenSelected(this.transferData.from_account_type_id);
            },
            hasValidToAccountTypeBeenSelected: function(){
                return this.hasValidAccountTypeBeenSelected(this.transferData.to_account_type_id);
            },
            listAccountTypes: function(){
                return _.orderBy(this.rawAccountTypesData, 'name');
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
            uploadToken: function(){
                return document.querySelector("meta[name='csrf-token']").getAttribute('content');
            }
        },
        methods: {
            decimaliseValue: function(){
                if(!_.isEmpty(this.transferData.value)){
                    let cleanedEntryValue = this.transferData.value.replace(/[^0-9.]/g, '');
                    this.transferData.value = parseFloat(cleanedEntryValue).toFixed(2);
                }
            },
            setModalState: function(modal){
                store.dispatch('currentModal', modal);
            },
            openModal: function(){
                this.setModalState(store.getters.STORE_MODAL_TRANSFER);
                this.isVisible = true;
                this.resetData();
                this.updateAccountTypeMeta('from');
                this.updateAccountTypeMeta('to');
            },
            closeModal: function(){
                this.setModalState(store.getters.STORE_MODAL_NONE);
                this.isVisible = false;
                this.resetData();
                this.updateAccountTypeMeta('from');
                this.updateAccountTypeMeta('to');
            },
            hasValidAccountTypeBeenSelected: function(accountTypeId){
                accountTypeId = parseInt(accountTypeId);
                return !isNaN(accountTypeId) || accountTypeId === this.accountTypeMeta.externalAccountTypeId;
            },
            canShowAccountTypeMeta: function(accountTypeId){
                accountTypeId = parseInt(accountTypeId);
                return this.hasValidAccountTypeBeenSelected(accountTypeId) && accountTypeId !== this.accountTypeMeta.externalAccountTypeId;
            },
            saveTransfer: function(){
                this.$eventBus.broadcast(this.$eventBus.EVENT_LOADING_SHOW());

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
                        this.$eventBus.broadcast(
                            this.$eventBus.EVENT_NOTIFICATION(),
                            {type: notification.type, message: notification.message}
                        );
                    }
                    this.$eventBus.broadcast(this.$eventBus.EVENT_ACCOUNT_UPDATE());
                    this.$eventBus.broadcast(this.$eventBus.EVENT_ENTRY_TABLE_UPDATE(), this.currentPage);
                    this.closeModal();
                }.bind(this));
            },
            updateAccountTypeMeta: function(accountTypeSelect){
                let account = this.accountTypesObject.getAccount(this.transferData[accountTypeSelect+'_account_type_id']);
                this.accountTypeMeta[accountTypeSelect].accountName = account.name;
                let accountType = this.accountTypesObject.find(this.transferData[accountTypeSelect+'_account_type_id']);
                this.accountTypeMeta[accountTypeSelect].lastDigits = accountType.last_digits;
                this.accountTypeMeta[accountTypeSelect].isEnabled = !account.disabled && !accountType.disabled;
            },
            resetData: function(){
              this.$eventBus.broadcast(this.$eventBus.EVENT_FILE_DROP_UPDATE(), {modal: 'transfer-modal', task: 'enable'});
              this.$eventBus.broadcast(this.$eventBus.EVENT_FILE_DROP_UPDATE(), {modal: 'transfer-modal', task: 'clear'});
              this.defaultData.date = this.currentDate;
              this.defaultData.attachments = [];  // for whatever reason clonedObject.push() also pushes to the original. This is a work around.
              this.transferData = _.clone(this.defaultData);
              this.accountTypeMeta.from = _.clone(this.accountTypeMeta.default);
              this.accountTypeMeta.to = _.clone(this.accountTypeMeta.default);
            }
        },
        created: function(){
            this.$eventBus.listen(this.$eventBus.EVENT_TRANSFER_MODAL_OPEN(), this.openModal);
            this.$eventBus.listen(this.$eventBus.EVENT_TRANSFER_MODAL_CLOSE(), this.closeModal);
        },
        mounted: function(){
            this.resetData();
        }
    }
</script>

<style lang="scss" scoped>
    .field-label.is-normal{
        font-size: 0.875rem;
    }
    .transfer-account-type{
        min-width: 20rem;
    }
    #transfer-memo{
        padding: 0.25rem 0.5rem;
    }
    .field:not(:last-child){
        margin-bottom: 0.5rem;
    }
</style>