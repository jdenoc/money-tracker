<template>
    <div id="transfer-modal" class="modal" v-bind:class="{'is-active': isVisible}" v-hotkey="keymap">
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
                        <input class="input has-text-grey-dark" id="transfer-value" name="transfer-value" type="text" placeholder="999.99"
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
                        <div class="help has-text-info" v-bind:class="{'is-hidden': !canShowFromAccountTypeMeta}">
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
                        <div class="help has-text-info" v-bind:class="{'is-hidden': !canShowToAccountTypeMeta}">
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
                        <voerro-tags-input
                            element-id="transfer-tags"
                            v-model="transferData.tags"
                            v-bind:existing-tags="listTags"
                            v-bind:only-existing-tags="true"
                            v-bind:typeahead="true"
                            v-bind:typeahead-max-results="5"
                        ></voerro-tags-input>
                    </div></div></div>
                </div>

                <div class="field"><div class="control">
                    <vue-dropzone ref="transferModalFileUpload" id="transfer-modal-file-upload"
                        v-bind:options="dropzoneOptions"
                        v-on:vdropzone-success="dropzoneSuccessfulUpload"
                        v-on:vdropzone-error="dropzoneUploadError"
                        v-on:vdropzone-removed-file="dropzoneRemoveUpload"
                    ></vue-dropzone>
                </div></div>
            </section>

            <footer class="modal-card-foot">
                <div class="container">
                    <div class="field is-grouped">
                        <div class="control is-expanded">
                        </div>
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
    import _ from 'lodash';
    import {AccountTypes} from "../account-types";
    import {Entry} from "../entry";
    import {SnotifyStyle} from 'vue-snotify';
    import {Tags} from '../tags';
    import Store from '../store';
    import VoerroTagsInput from '@voerro/vue-tagsinput';
    import vue2Dropzone from 'vue2-dropzone';

    export default {
        name: "transfer-modal",
        components: {
            VoerroTagsInput,
            VueDropzone: vue2Dropzone,
        },
        data: function(){
            return {
                accountTypesObject: new AccountTypes(),
                entryObject: new Entry(),
                tagsObject: new Tags(),

                accountTypeMeta: {
                    default: {
                        accountName: "",
                        lastDigits: "",
                    },
                    from: {
                        accountName: "",
                        lastDigits: "",
                    },
                    to: {
                        accountName: "",
                        lastDigits: "",
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

                toggleButtonProperties: {
                    colors: {'checked': '#ffcc00', 'unchecked': '#00d1b2'},
                    height: 40,
                    labels: {'checked': 'Expense', 'unchecked': 'Income'},
                    width: 200,
                },
            }
        },
        computed: {
            areAccountTypesSet: function(){
                return this.listAccountTypes.length > 0;
            },
            areTagsSet: function(){
                return !_.isEmpty(this.listTags);
            },
            canShowFromAccountTypeMeta: function(){
                return this.canShowAccountTypeMeta(this.transferData.from_account_type_id);
            },
            canShowToAccountTypeMeta: function(){
                return this.canShowAccountTypeMeta(this.transferData.to_account_type_id);
            },
            currentPage: function(){
                return Store.getters.currentPage;
            },
            hasValidFromAccountTypeBeenSelected: function(){
                return this.hasValidAccountTypeBeenSelected(this.transferData.from_account_type_id);
            },
            hasValidToAccountTypeBeenSelected: function(){
                return this.hasValidAccountTypeBeenSelected(this.transferData.to_account_type_id);
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
            listAccountTypes: function(){
                let accountTypes = this.accountTypesObject.retrieve;
                return _.orderBy(accountTypes, 'name');
            },
            dropzoneRef: function(){
                return this.$refs.transferModalFileUpload;
            },
            dropzoneOptions: function(){
                return {
                    url: '/attachment/upload',
                    method: 'post',
                    addRemoveLinks: true,
                    paramName: 'attachment',
                    params: {_token: this.uploadToken},
                    dictDefaultMessage: '<span class="icon"><i class="fas fa-cloud-upload-alt"></i></span><br/>Drag & Drop',
                    hiddenInputContainer: "#transfer-modal",
                    init: function(){
                        document.querySelector('#transfer-modal .dz-hidden-input').setAttribute('id', 'transfer-modal-hidden-file-input');
                    }
                }
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
            openModal: function(){
                this.isVisible = true;
                this.resetData();
                this.updateAccountTypeMeta('from');
                this.updateAccountTypeMeta('to');
            },
            closeModal: function(){
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
                    this.transferData.tags.forEach(function(tagId){
                        // each "tag" MUST be an int
                        if(!_.isArray(tagId) && _.isNumber(parseInt(tagId))){
                            transferData.tags.push(tagId);
                        }
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
                    this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, this.currentPage);
                    this.closeModal();
                }.bind(this));
            },
            notAvailable: function(){
                alert("This feature is not currently available");
            },
            warningAlert: function(){
                alert("WARNING: This feature is still in beta. Expect unintended consequences.");
            },
            updateAccountTypeMeta: function(accountTypeSelect){
                let account = this.accountTypesObject.getAccount(this.transferData[accountTypeSelect+'_account_type_id']);
                this.accountTypeMeta[accountTypeSelect].accountName = account.name;
                let accountType = this.accountTypesObject.find(this.transferData[accountTypeSelect+'_account_type_id']);
                this.accountTypeMeta[accountTypeSelect].lastDigits = accountType.last_digits;
            },
            resetData: function(){
                this.dropzoneRef.removeAllFiles();
                this.defaultData.attachments = [];  // for whatever reason clonedObject.push() also pushes to the original. This is a work around.
                this.transferData = _.clone(this.defaultData);
                this.accountTypeMeta.from = _.clone(this.accountTypeMeta.default);
                this.accountTypeMeta.to = _.clone(this.accountTypeMeta.default);
            },
            keymap: function(){
                return {
                    'ctrl+esc': function(){
                        console.log('transfer:ctrl+esc');
                        this.closeModal();
                    }.bind(this)
                };
            },
            dropzoneSuccessfulUpload(file, response){
                // response: {'uuid', 'name', 'tmp_filename'}
                this.transferData.attachments.push(response);
                this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.info, message: "uploaded: "+response.name});
            },
            dropzoneUploadError(file, message, xhr){
                // response: {'error'}
                this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.warning, message: "file upload failure: "+message.error});
            },
            dropzoneRemoveUpload(file){
                let removedAttachmentObject = JSON.parse(file.xhr.response);
                this.transferData.attachments = this.transferData.attachments.filter(function(attachment){
                    return attachment.uuid !== removedAttachmentObject.uuid;
                });
            }
        },
        created: function(){
            this.$eventHub.listen(this.$eventHub.EVENT_TRANSFER_MODAL_OPEN, this.openModal);
        },
        mounted: function(){
            this.resetData();
        }
    }
</script>

<style scoped>
    .is-checkradio[type=checkbox].is-block+label::after,
    .is-checkradio[type=checkbox].is-block+label:after{
        top: 0.4rem;
    }
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