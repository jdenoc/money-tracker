<template>
    <div id="entry-modal" class="modal" v-bind:class="{'is-active': isVisible}">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">
                    Entry: <span v-if="entryData.id" v-text="entryData.id"></span><span v-else>new</span>
                    <button type="button" id="entry-transfer-btn" class="button is-small is-outlined"
                        v-if="isTransfer"
                        v-on:click="primeDataForModal(entryData.transfer_entry_id)"
                    >
                        <span class="icon is-small"><i class="fas fa-exchange-alt"></i></span>
                    </button>
                </p>
                <input type="hidden" name="entry-id" id="entry-id" v-model="entryData.id" />

                <div class="control">
                    <input class="is-checkradio is-block is-success" id="entry-confirm" type="checkbox" name="entry-confirm"
                        v-model="entryData.confirm"
                        v-bind:disabled="isLocked"
                    />
                    <label for="entry-confirm" class="checkbox-adjusted-top"
                        v-bind:class="{'has-text-grey-light': !entryData.confirm, 'has-text-white': entryData.confirm}"
                        >Confirmed</label>
                </div>

                <button class="delete" aria-label="close" v-on:click="closeModal"></button>
            </header>

            <section class="modal-card-body">
                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="entry-date">Date:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <input class="input has-text-grey-dark" id="entry-date" name="entry-date" type="date"
                            v-model="entryData.entry_date"
                            v-bind:readonly="isLocked"
                        />
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="entry-value">Value:</label></div>
                    <div class="field-body"><div class="field"><div class="control has-icons-left">
                        <input class="input has-text-grey-dark" id="entry-value" name="entry-value" type="text" placeholder="999.99"
                           v-model="entryData.entry_value"
                           v-bind:readonly="isLocked"
                           v-on:change="decimaliseEntryValue"
                        />
                        <span class="icon is-left"><i class="fas" v-bind:class="accountTypeMeta.currencyClass"></i></span>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="entry-account-type">Account Type:</label></div>
                    <div class="field-body"><div class="field">
                        <div class="control"><div class="select" v-bind:class="{'is-loading': !areAccountTypesSet}">
                            <select name="entry-account-type" id="entry-account-type" class="has-text-grey-dark"
                                v-model="entryData.account_type_id"
                                v-on:change="updateAccountTypeMeta"
                                v-bind:disabled="isLocked"
                                >
                                <option></option>
                                <option
                                    v-for="accountType in listAccountTypes"
                                    v-bind:key="accountType.id"
                                    v-bind:value="accountType.id"
                                    v-text="accountType.name"
                                    v-show="!accountType.disabled"
                                ></option>
                            </select>
                        </div></div>
                        <div id="entry-account-type-meta" class="help" v-bind:class="{'is-hidden': !hasAccountTypeBeenSelected, 'has-text-info': isAccountEnabled, 'has-text-grey-light': !isAccountEnabled}">
                            <p>
                                <span class="has-text-weight-semibold has-padding-right">Account Name:</span>
                                <span id="entry-account-type-meta-account-name" v-text="accountTypeMeta.accountName"></span>
                            </p>
                            <p>
                                <span class="has-text-weight-semibold has-padding-right">Last 4 Digits:</span>
                                <span class="entry-account-type-meta-last-digits" v-text="accountTypeMeta.lastDigits"></span>
                            </p>
                        </div>
                    </div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="entry-memo">Memo:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <textarea id="entry-memo" name="entry-memo" class="textarea has-text-grey-dark"
                            v-model="entryData.memo"
                            v-bind:readonly="isLocked"
                        ></textarea>
                    </div></div></div>
                </div>

                <div class="field">
                    <div class="control has-text-centered">
                        <toggle-button
                            id="entry-expense"
                            v-model="entryData.expense"
                            v-bind:color="toggleButtonProperties.colors"
                            v-bind:disabled="isLocked"
                            v-bind:labels="toggleButtonProperties.labels"
                            v-bind:height="toggleButtonProperties.height"
                            v-bind:value="entryData.expense"
                            v-bind:sync="true"
                            v-bind:width="toggleButtonProperties.width"
                        />
                    </div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label">Tags:</label></div>
                    <div class="field-body"><div class="field"><div class="control" v-bind:class="{'is-loading': !areTagsSet}">
                        <voerro-tags-input
                            v-show="!isLocked"
                            element-id="entry-tags"
                            v-model="entryData.tags"
                            v-bind:existing-tags="listTags"
                            v-bind:only-existing-tags="true"
                            v-bind:typeahead="true"
                            v-bind:typeahead-max-results="5"
                        ></voerro-tags-input>
                        <div class="box" v-show="isLocked"><div class="tags">
                            <span class="tag"
                                v-for="tag in displayReadOnlyTags"
                                v-text="tag"
                            ></span>
                        </div></div>
                    </div></div></div>
                </div>

                <div class="field"><div class="control">
                    <vue-dropzone ref="entryModalFileUpload" id="entry-modal-file-upload"
                        v-bind:options="dropzoneOptions"
                        v-on:vdropzone-success="dropzoneSuccessfulUpload"
                        v-on:vdropzone-error="dropzoneUploadError"
                        v-on:vdropzone-removed-file="dropzoneRemoveUpload"
                        v-show="!isLocked"
                    ></vue-dropzone>
                </div></div>

                <div id="existing-entry-attachments" class="field">
                    <entry-modal-attachment
                        v-for="entryAttachment in orderedAttachments"
                        v-if="!entryAttachment.tmp_filename"
                        v-bind:key="entryAttachment.uuid"
                        v-bind:uuid="entryAttachment.uuid"
                        v-bind:name="entryAttachment.name"
                        v-bind:entryId="entryData.id"
                    ></entry-modal-attachment>
                </div>
            </section>

            <footer class="modal-card-foot">
                <div class="container">
                    <div class="field is-grouped">
                        <div class="control is-expanded">
                            <button type="button" id="entry-delete-btn" class="button is-danger"
                                v-bind:class="{'is-hidden': !isDeletable}"
                                v-on:click="deleteEntry"
                                >
                                <i class="fas fa-trash-alt has-padding-right"></i>Delete
                            </button>
                        </div>
                        <div class="control">
                            <button type="button" id="entry-lock-btn" class="button"
                                v-bind:class="{'is-hidden': !isConfirmed}"
                                v-on:click="toggleLockState"
                                >
                                <i class="fas" v-bind:class="{'fa-unlock-alt': isLocked, 'fa-lock': !isLocked}"></i>
                            </button>
                            <button type="button" id="entry-cancel-btn" class="button" v-on:click="closeModal">Cancel</button>
                            <button type="button" id="entry-save-btn" class="button is-success"
                                v-show="!isLocked"
                                v-on:click="saveEntry"
                                v-bind:disabled="!canSave"
                                >
                                <i class="fas fa-check has-padding-right"></i>Save changes
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
    import {AccountTypes} from "../account-types";
    import {Entry} from "../entry";
    import {SnotifyStyle} from 'vue-snotify';
    import {Tags} from '../tags';
    import EntryModalAttachment from "./entry-modal-attachment";
    import Store from '../store';
    import { ToggleButton } from 'vue-js-toggle-button';
    import VoerroTagsInput from '@voerro/vue-tagsinput';
    import vue2Dropzone from 'vue2-dropzone';

    export default {
        name: "entry-modal",
        components: {
            EntryModalAttachment,
            ToggleButton,
            VoerroTagsInput,
            VueDropzone: vue2Dropzone,
        },
        data: function(){
            return {
                accountTypesObject: new AccountTypes(),
                entryObject: new Entry(),
                tagsObject: new Tags(),

                accountTypeMeta: {
                    accountName: "",
                    currencyClass:"fa-dollar-sign",
                    lastDigits: "",
                    isEnabled: true
                },

                isDeletable: false,
                isLocked: false,
                isVisible: false,

                entryData: {}, // this gets filled with values from defaultData

                defaultData: {
                    id: null,
                    entry_date: "",
                    account_type_id: "",
                    entry_value: "",
                    memo: "",
                    expense: true,
                    confirm: false,
                    transfer_entry_id: null,
                    tags: [],
                    attachments: []
                },

                currency: {
                    euro:     {label: "EUR", class: "fa-euro-sign"},
                    dollarUs: {label: "USD", class: "fa-dollar-sign"},
                    dollarCa: {label: "CAD", class: "fa-dollar-sign"},
                    pound:    {label: "GBP", class: "fa-pound-sign"}
                },

                toggleButtonProperties: {
                    colors: {checked: '#ffcc00', unchecked: '#00d1b2'},
                    height: 40,
                    labels: {checked: 'Expense', unchecked: 'Income'},
                    width: 200,
                },
            }
        },
        computed: {
            currentPage: function(){
                return Store.getters.currentPage;
            },
            isConfirmed: function(){
                return this.entryData.confirm && this.entryData.id;
            },
            isTransfer: function(){
                return _.isNumber(this.entryData.transfer_entry_id);
            },
            areAccountTypesSet: function(){
                return this.listAccountTypes.length > 0;
            },
            hasAccountTypeBeenSelected: function(){
                return this.entryData.account_type_id !== '';
            },
            isAccountEnabled: function(){
                return this.accountTypeMeta.isEnabled;
            },
            getAttachmentUploadUrl: function(){
                return this.dropzoneOptions.url;
            },
            listTags: function(){
                return this.tagsObject.retrieve.reduce(function(result, item){
                    result[item.id] = item.name;
                    return result;
                }, {});
            },
            areTagsSet: function(){
                return !_.isEmpty(this.listTags);
            },
            displayReadOnlyTags: function(){
                let currentTags = typeof this.entryData.tags == 'undefined' ? [] : this.entryData.tags;
                return currentTags.map(function(item){ return this.listTags[item]; }.bind(this));
            },
            listAccountTypes: function(){
                let accountTypes = this.accountTypesObject.retrieve;
                return _.orderBy(accountTypes, 'name');
            },
            currentDate: function(){
                let today = new Date();
                return today.getFullYear()+'-'
                    +(today.getMonth()<9?'0':'')+(today.getMonth()+1)+'-'	// months in JavaScript start from 0=January
                    +(today.getDate()<10?'0':'')+today.getDate();
            },
            dropzoneRef: function(){
                return this.$refs.entryModalFileUpload;
            },
            dropzoneOptions: function(){
                return {
                    url: '/attachment/upload',
                    method: 'post',
                    addRemoveLinks: true,
                    paramName: 'attachment',
                    params: {_token: this.uploadToken},
                    dictDefaultMessage: '<span class="icon"><i class="fas fa-cloud-upload-alt"></i></span><br/>Drag & Drop',
                    hiddenInputContainer: '#entry-modal',
                    init: function(){
                        document.querySelector('#entry-modal .dz-hidden-input').setAttribute('id', 'entry-modal-hidden-file-input');
                    }
                }
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
            uploadToken: function(){
                return document.querySelector("meta[name='csrf-token']").getAttribute('content');
            },
            orderedAttachments: function(){
                return _.orderBy(this.entryData.attachments, 'name')
            }
        },
        methods: {
            decimaliseEntryValue: function(){
                if(!_.isEmpty(this.entryData.entry_value)){
                    let cleanedEntryValue = this.entryData.entry_value.replace(/[^0-9.]/g, '');
                    this.entryData.entry_value = parseFloat(cleanedEntryValue).toFixed(2);
                }
            },
            setModalState: function(modal){
                Store.dispatch('currentModal', modal);
            },
            openModal: function(entryData = {}){
                this.setModalState(Store.getters.STORE_MODAL_ENTRY);
                if(!_.isEmpty(entryData)){
                    this.entryData = _.clone(entryData);
                    // our input-tags field requires that tag values are strings
                    this.entryData.tags = this.entryData.tags.map(function(tag){
                        return tag.id.toString();
                    });
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
            closeModal: function(){
                this.setModalState(Store.getters.STORE_MODAL_NONE);
                this.isDeletable = false;
                this.isVisible = false;
                this.resetEntryData();
                this.unlockModal();
                this.updateAccountTypeMeta();
            },
            primeDataForModal: function(entryId = null){
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
                if(!_.isEmpty(entryId) || _.isNumber(entryId)){ // isNumber is used to handle isEmpty() reading numbers as empty
                    if(_.isObject(entryId)){
                        // entryId was passed as part of an event payload
                        entryId = entryId[0];
                    }
                    new Promise(function(resolve, reject){
                        let entryData = this.entryObject.find(entryId);
                        if(this.entryObject.isEntryCurrent(entryData)){
                            resolve(entryData);
                        } else {
                            reject(entryId);
                        }
                    }.bind(this))
                        .then(this.openModal)       // resolve
                        .catch(function(entryId){   // reject
                            this.entryObject.fetch(entryId)
                                .then(function(fetchResult){
                                    let freshlyFetchedEntryData = {};
                                    if(fetchResult.fetched){
                                        freshlyFetchedEntryData = this.entryObject.find(entryId);
                                    }
                                    this.openModal(freshlyFetchedEntryData);
                                    if(!_.isEmpty(fetchResult.notification)){
                                        this.$eventHub.broadcast(
                                            this.$eventHub.EVENT_NOTIFICATION,
                                            {type: fetchResult.notification.type, message: fetchResult.notification.message}
                                        );
                                    }
                                }.bind(this));
                        }.bind(this));
                } else {
                    this.openModal({});
                }
            },
            toggleLockState: function(){
                if(this.isLocked){
                    this.unlockModal();
                } else {
                    this.lockModal();
                }
            },
            lockModal: function(){
                this.isLocked = true;
                this.dropzoneRef.disable();
            },
            unlockModal: function(){
                this.isLocked = false;
                this.dropzoneRef.enable();
                this.updateAccountTypeMeta();
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
                    this.entryData.tags.forEach(function(tagId){
                        // each "tag" MUST be an int
                        if(!_.isArray(tagId) && _.isNumber(parseInt(tagId))){
                            newEntryData.tags.push(tagId);
                        }
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
                        if(!_.isEmpty(notification)){
                            this.$eventHub.broadcast(
                                this.$eventHub.EVENT_NOTIFICATION,
                                {type: notification.type, message: notification.message.replace('%s', this.entryData.id)}
                            );
                        }
                        this.$eventHub.broadcast(this.$eventHub.EVENT_ACCOUNT_UPDATE);
                        this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, this.currentPage);
                    }.bind(this))
                    .finally(this.closeModal.bind(this));
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
                            this.$eventHub.broadcast(this.$eventHub.EVENT_ACCOUNT_UPDATE);
                            this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, this.currentPage);
                            // don't need to broadcast an event to hide the loading modal here
                            // already taken care of at the end of the entry-table update event process
                        } else {
                            this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
                        }
                    }.bind(this));
            },
            updateAccountTypeMeta: function(){
                let account = this.accountTypesObject.getAccount(this.entryData.account_type_id);
                this.accountTypeMeta.accountName = account.name;
                let accountType = this.accountTypesObject.find(this.entryData.account_type_id);
                this.accountTypeMeta.lastDigits = accountType.last_digits;
                this.accountTypeMeta.isEnabled = !accountType.disabled && !account.disabled;

                switch(account.currency){
                    case this.currency.euro.label:
                        this.accountTypeMeta.currencyClass = this.currency.euro.class;
                        break;

                    case this.currency.pound.label:
                        this.accountTypeMeta.currencyClass = this.currency.pound.class;
                        break;

                    case this.currency.dollarCa.label:
                        this.accountTypeMeta.currencyClass = this.currency.dollarCa.class;
                        break;

                    case this.currency.dollarUs.label:
                    default:
                        this.accountTypeMeta.currencyClass = this.currency.dollarUs.class;
                }
            },
            resetEntryData: function(){
                this.dropzoneRef.removeAllFiles();
                this.defaultData.entry_date = this.currentDate;
                this.defaultData.attachments = [];  // for whatever reason clonedObject.push() also pushes to the original. This is a work around.
                this.entryData = _.clone(this.defaultData);
            },
            dropzoneSuccessfulUpload(file, response){
                // response: {'uuid', 'name', 'tmp_filename'}
                this.entryData.attachments.push(response);
                this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.info, message: "uploaded: "+response.name});
            },
            dropzoneUploadError(file, message, xhr){
                // response: {'error'}
                this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.warning, message: "file upload failure: "+message.error});
            },
            dropzoneRemoveUpload(file){
                let removedAttachmentObject = JSON.parse(file.xhr.response);
                this.entryData.attachments = this.entryData.attachments.filter(function(attachment){
                    return attachment.uuid !== removedAttachmentObject.uuid;
                });
            }
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

<style scoped>
    .field-label.is-normal{
        font-size: 13px;
    }
    #entry-value{
        padding-left: 1.75rem;
    }
    #entry-account-type{
        min-width: 250px;
    }
    #entry-memo{
        padding: 0.25rem 0.5rem;
    }
    .vue-js-switch#entry-expense{
        font-size: 1.25rem;
    }
    .field:not(:last-child) {
        margin-bottom: 0.5rem;
    }
</style>