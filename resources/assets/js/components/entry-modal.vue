<template>
    <div class="modal" v-bind:class="{'is-active': isVisible}">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Entry: <span id="entry-id-display">new</span></p>
                <input type="hidden" name="entry-id" id="entry-id" />

                <div class="control">
                    <input class="is-checkradio is-block is-success" id="entry-confirm" type="checkbox" name="entry-confirm" v-model="entryData.confirm" >
                    <label for="entry-confirm" v-bind:class="{'has-text-grey-light': !isConfirmed, 'has-text-white': isConfirmed}">Confirmed</label>
                </div>

                <button class="delete" aria-label="close" v-on:click="closeModal"></button>
            </header>

            <!-- TODO: finish building entry-modal -->

            <section class="modal-card-body">
                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="entry-date">Date:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <input class="input" id="entry-date" name="entry-date" type="date"/>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="entry-value">Value:</label></div>
                    <div class="field-body"><div class="field"><div class="control has-icons-left">
                        <input class="input" id="entry-value" name="entry-value" type="text" placeholder="999.99"/>
                        <span class="icon is-left"><i class="fas fa-dollar-sign"></i></span>
                    </div></div></div>
                </div>

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label" for="entry-account-type">Account Type:</label></div>
                    <div class="field-body"><div class="field">
                        <div class="control"><div class="select" v-bind:class="{'is-loading': !areAccountTypesSet}">
                            <select name="entry-account-type" id="entry-account-type"
                                v-model="entryData.account_type_id"
                                v-on:change="updateAccountTypeMeta"
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
                        <textarea id="entry-memo" name="entry-memo" class="textarea"></textarea>
                    </div></div></div>
                </div>

                <!-- TODO: "entry.expense" should be some sort of switch -->
                <!--<div id="entry-expense-switch-container"><input type="checkbox" name="expense-switch" checked /></div>-->

                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label">Tags:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <voerro-tags-input
                            element-id="entry-tags"
                            v-model="entryData.tags"
                            v-bind:existing-tags="listTags"
                            v-bind:only-existing-tags="true"
                            v-bind:typeahead="true"
                            v-bind:typeahead-max-results="5"
                        ></voerro-tags-input>
                    </div></div></div>
                </div>

                <div class="field"><div class="control">
                    <vue-dropzone ref="entryModalFileUpload" id="entry-modal-file-upload"
                        v-bind:options="dropzoneOptions"
                    ></vue-dropzone>
                </div></div>

            </section>

            <footer class="modal-card-foot">
                <div class="container">
                    <div class="field is-grouped">
                        <div class="control is-expanded">
                            <button type="button" class="button is-danger" v-bind:class="{'is-hidden': isDeletable}" v-on:click="deleteEntry">
                                <i class="fas fa-trash-alt has-padding-right"></i>Delete
                            </button>
                        </div>
                        <div class="control">
                            <button type="button" class="button" v-bind:class="{'is-hidden': !isConfirmed}">
                                <i class="fas" v-bind:class="{'fa-unlock-alt': isLocked, 'fa-lock': !isLocked}"></i>
                            </button>
                            <button type="button" class="button" v-on:click="closeModal">Cancel</button>
                            <button type="button" class="button is-success"><i class="fas fa-check has-padding-right"></i>Save changes</button>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    import {AccountTypes} from "../account-types";
    import {Tags} from '../tags';
    import VoerroTagsInput from '@voerro/vue-tagsinput';
    import vue2Dropzone from 'vue2-dropzone';

    export default {
        name: "entry-modal",
        components: {
            VoerroTagsInput,
            VueDropzone: vue2Dropzone,
        },
        data: function(){
            // TODO: include default entry data that can be used to overwrite current data when modal closes
            // TODO: verify these default values are OK
            return {
                accountTypesObject: new AccountTypes(),
                tagsObject: new Tags(),

                accountTypeMeta: {
                    accountName: "",
                    lastDigits: ""
                },

                isVisible: true,
                isLocked: true,
                entryData: {
                    account_type_id: '',
                    confirm: false,
                    tags: []
                },

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
        // TODO: when not onFocus for the entry value, convert to number decimal (2 places)
        computed: {
            isConfirmed: function(){
                return this.entryData.confirm;
            },
            isDeletable: function(){
                // TODO: true ONLY if an existing entry
                return true;
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
            listAccountTypes: function(){
                return this.accountTypesObject.retrieve.sort(function(a, b){
                    // sorts account-type objects by name
                    let comparison = 0;
                    if(a.name > b.name){
                        comparison = 1;
                    } else if(b.name > a.name){
                        comparison = -1;
                    }
                    return comparison;
                });
            },
            todaysDate: function(){
                let today = new Date();
                return today.getFullYear()+'-'
                    +(today.getMonth()<9?'0':'')+(today.getMonth()+1)+'-'	// months in JavaScript start from 0=January
                    +(today.getDate()<10?'0':'')+today.getDate();
            }
        },
        methods: {
            // TODO: toggle "locking" entry-modal
            // TODO: set all fields to read-only when entry-modal "locked"

            // TODO: set modal date to current date on init

            openModal: function(){
                this.isVisible = true;
                this.warningAlert();
                // TODO: trigger opening this modal from a different component (i.e.: event)

                // TODO: open modal with entry data displayed, if any

                // TODO: open modal with default value if none provided
            },
            closeModal: function(){
                // TODO: closing modal causes entryData to be overwritten with default data
                // TODO: set modal date to current date on close
                this.warningAlert();
                this.isLocked = true;
                this.isVisible = false;
                this.resetEntryData();
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
            },
            resetEntryData: function(){
                this.defaultData.entry_date = this.todaysDate;
                this.entryData = this.defaultData;
            }
        },
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
    #entry-account-type{
        min-width: 200px;
    }
    .has-padding-right{
        padding-right: 5px;
    }
</style>