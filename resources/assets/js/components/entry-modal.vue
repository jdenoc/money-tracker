<template>
    <div class="modal" v-bind:class="{'is-active': isVisible}">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Entry: <span v-if="entryData.id" v-text="entryData.id"></span><span v-else>new</span></p>
                <input type="hidden" name="entry-id" id="entry-id" v-model="entryData.id" />

                <div class="control">
                    <input class="is-checkradio is-block is-success" id="entry-confirm" type="checkbox" name="entry-confirm"
                        v-model="entryData.confirm"
                        v-bind:disabled="isLocked"
                    >
                    <label for="entry-confirm"
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
                        <div class="help has-text-info" v-bind:class="{'is-hidden': !hasAccountTypeBeenSelected}">
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
                    <div class="field-body"><div class="field"><div class="control">
                        <voerro-tags-input
                            v-show="!isLocked"
                            element-id="entry-tags"
                            v-model="entryData.tags"
                            v-bind:existing-tags="listTags"
                            v-bind:only-existing-tags="true"
                            v-bind:typeahead="true"
                            v-bind:typeahead-max-results="5"
                        ></voerro-tags-input>
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> Rather than an input for "readonly" tags within the entry-modal, we're going to use `.box` and `.tag` class elements.
                        <div class="box" v-show="isLocked"><div class="tags">
                            <span class="tag"
                                v-for="tag in displayReadOnlyTags"
                                v-text="tag"
                            ></span>
                        </div></div>
<<<<<<< HEAD
=======
                        <input type="text" class="input has-text-grey-dark" readonly
                           v-model="displayReadOnlyTags"
                           v-show="isLocked"
                        />
>>>>>>> Applied a toggled locking feature to the entry-modal for previously saved & confirmed entries.
=======
>>>>>>> Rather than an input for "readonly" tags within the entry-modal, we're going to use `.box` and `.tag` class elements.
                    </div></div></div>
                </div>

                <div class="field"><div class="control">
                    <vue-dropzone ref="entryModalFileUpload" id="entry-modal-file-upload"
                        v-bind:options="dropzoneOptions"
                        v-show="!isLocked"
                    ></vue-dropzone>
                </div></div>

                <div class="field">
                    <entry-modal-attachment
                        v-for="entryAttachment in entryData.attachments"
                        v-bind:key="entryAttachment.uuid"
                        v-bind:id="entryAttachment.uuid"
                        v-bind:name="entryAttachment.name"
                    ></entry-modal-attachment>
                </div>
            </section>

            <footer class="modal-card-foot">
                <div class="container">
                    <div class="field is-grouped">
                        <div class="control is-expanded">
                            <button type="button" class="button is-danger"
                                v-bind:class="{'is-hidden': !isDeletable}"
                                v-on:click="deleteEntry"
                                >
                                <i class="fas fa-trash-alt has-padding-right"></i>Delete
                            </button>
                        </div>
                        <div class="control">
                            <button type="button" class="button"
                                v-bind:class="{'is-hidden': !isConfirmed}"
                                v-on:click="toggleLockState"
                                >
                                <i class="fas" v-bind:class="{'fa-unlock-alt': isLocked, 'fa-lock': !isLocked}"></i>
                            </button>
                            <button type="button" class="button" v-on:click="closeModal">Cancel</button>
                            <button type="button" class="button is-success"
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
    import {Entries} from "../entries"
    import {Entry} from "../entry"
    import {Tags} from '../tags';
    import EntryModalAttachment from "./entry-modal-attachment";
    import ToggleButton from 'vue-js-toggle-button/src/Button'
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
                entriesObject: new Entries(),
                entryObject: new Entry(),
                tagsObject: new Tags(),

                accountTypeMeta: {
                    accountName: "",
                    currencyClass:"fa-dollar-sign",
                    lastDigits: "",
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
                    tags: [],
                    attachments: []
                },

                toggleButtonProperties: {
                    colors: {'checked': '#ffcc00', 'unchecked': '#00d1b2'},
                    height: 40,
                    labels: {'checked': 'Expense', 'unchecked': 'Income'},
                    width: 200,
                },

                dropzoneOptions: {
                    url: '/attachment/upload',
                    method: 'post',
                    addRemoveLinks: true,
                    paramName: 'attachment',
                    params: {_token: uploadToken},
                    dictDefaultMessage: '<span class="icon"><i class="fas fa-cloud-upload-alt"></i></span><br/>Drag & Drop'
                },
            }
        },
        computed: {
            isConfirmed: function(){
                return this.entryData.confirm && this.entryData.id;
            },
            areAccountTypesSet: function(){
                return this.listAccountTypes.length > 0;
            },
            hasAccountTypeBeenSelected: function(){
                return this.entryData.account_type_id !== '';
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
        },
        methods: {
            decimaliseEntryValue: function(){
                if(!_.isEmpty(this.entryData.entry_value)){
                    let cleanedEntryValue = this.entryData.entry_value.replace(/[^0-9.]/g, '');
                    this.entryData.entry_value = parseFloat(cleanedEntryValue).toFixed(2);
                }
            },
            openModal: function(entryData = {}){
                this.entryData = _.clone(entryData);
                if(!_.isEmpty(this.entryData)){
                    // our input-tags field requires that tag values are strings
                    this.entryData.tags = this.entryData.tags.map(function(tag){
                        return tag.id.toString();
                    });
                    this.entryData.confirm ? this.lockModal() : this.unlockModal();
                    this.isDeletable = true;
                } else {
                    this.isDeletable = false;
                }
                this.isVisible = true;
                this.updateAccountTypeMeta();
            },
            closeModal: function(){
                this.isDeletable = false;
                this.isVisible = false;
                this.resetEntryData();
                this.unlockModal();
                this.updateAccountTypeMeta();
            },
            primeDataForModal: function(entryId = null){
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
                        .then(this.openModal)
                        .catch(function(entryId){
                            this.entryObject.fetch(entryId)
                                .then(function(){
                                    let entryData = this.entryObject.find(entryId);
                                    this.openModal(entryData);
                                }.bind(this))
                                .catch(function(){
                                    // TODO: display notification error
                                    this.openModal({});
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
            },
            saveEntry: function(){
                // TODO: save an entry
                this.notAvailable();
            },
            deleteEntry: function(){
                // TODO: delete entry, but ONLY if entry does not already exist
                this.notAvailable();
            },
            notAvailable: function(){
                alert("This feature is not currently available");
            },
            warningAlert: function(){
                alert("WARNING: This feature is still in beta. Expect unintended consequences.");
            },
            updateAccountTypeMeta: function(){
                let account = this.accountTypesObject.getAccount(this.entryData.account_type_id);
                this.accountTypeMeta.accountName = account.name;
                let accountType = this.accountTypesObject.find(this.entryData.account_type_id);
                this.accountTypeMeta.lastDigits = accountType.last_digits;

                switch(account.currency){
                    case 'EUR':
                        this.accountTypeMeta.currencyClass = "fa-euro-sign";
                        break;

                    case 'GBP':
                        this.accountTypeMeta.currencyClass = "fa-pound-sign";
                        break;

                    case 'USD':
                    case 'CAD':
                    default:
                        this.accountTypeMeta.currencyClass = 'fa-dollar-sign';
                }
            },
            resetEntryData: function(){
                this.defaultData.entry_date = this.currentDate;
                this.entryData = _.clone(this.defaultData);
            }
        },
        created: function(){
            this.$eventHub.listen(this.$eventHub.EVENT_OPEN_ENTRY_MODAL, this.primeDataForModal);
        },
        mounted: function(){
            this.resetEntryData();
        }
    }
</script>

<style scoped>
    .is-checkradio[type=checkbox].is-block+label::after,
    .is-checkradio[type=checkbox].is-block+label:after{
        top: 0.4rem;
    }
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