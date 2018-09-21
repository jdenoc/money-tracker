<template>
    <div class="modal" v-bind:class="{'is-active': isVisible}">
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
                                <option value="-1">[External account]</option>
                            </select>
                        </div></div>
                        <div class="help has-text-info" v-bind:class="{'is-hidden': !hasValidFromAccountTypeBeenSelected}">
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
                                <option value="-1">[External account]</option>
                            </select>
                        </div></div>
                        <div class="help has-text-info" v-bind:class="{'is-hidden': !hasValidToAccountTypeBeenSelected}">
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
                    <div class="field-body"><div class="field"><div class="control">
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
                    ></vue-dropzone>
                </div></div>
            </section>

            <footer class="modal-card-foot">
                <div class="container">
                    <div class="field is-grouped">
                        <div class="control is-expanded">
                        </div>
                        <div class="control">
                            <button type="button" class="button" v-on:click="closeModal">Cancel</button>
                            <button type="button" class="button is-success"
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
    import {Entries} from "../entries"
    import {Tags} from '../tags';
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
                entriesObject: new Entries(),
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
                    }
                },

                isVisible: false,

                transferData: {}, // this gets filled with values from defaultData

                defaultData: {
                    date: "",
                    value: "",
                    memo: "",
                    from_account_type_id: "",
                    to_account_type_id: "",
                    expense: true,
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
            areAccountTypesSet: function(){
                return this.listAccountTypes.length > 0;
            },
            hasValidFromAccountTypeBeenSelected: function(){
                let accountTypeId = parseInt(this.transferData.from_account_type_id);
                return !isNaN(accountTypeId) && accountTypeId !== -1;
            },
            hasValidToAccountTypeBeenSelected: function(){
                let accountTypeId = parseInt(this.transferData.to_account_type_id);
                return !isNaN(accountTypeId) && accountTypeId !== -1;
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
            canSave: function(){
                if(isNaN(Date.parse(this.transferData.date))){
                    return false;
                }
                let transferValue = _.toNumber(this.transferData.value);
                if(this.transferData.value === "" || isNaN(transferValue) || !_.isNumber(transferValue)){
                    return false;
                }
                if(!this.hasValidFromAccountTypeBeenSelected && parseInt(this.transferData.from_account_type_id) !== -1){
                    return false;
                }
                if(!this.hasValidToAccountTypeBeenSelected && parseInt(this.transferData.to_account_type_id) !== -1){
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
            saveTransfer: function(){
                // TODO: if "from" == external account:
                // TODO:    ignore
                // TODO: else:
                // TODO:    save "from" entry (expense=1)
                // TODO:    remember "from" ID
                // TODO: if "to" == external account:
                // TODO:    ignore
                // TODO: else:
                // TODO:    save "to" entry (expense=0)
                // TODO:    remember "to" ID
                // TODO: save entry_transfer
                this.notAvailable();
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
                this.transferData = _.clone(this.defaultData);
                this.accountTypeMeta.from = _.clone(this.accountTypeMeta.default);
                this.accountTypeMeta.to = _.clone(this.accountTypeMeta.default);
            }
        },
        created: function(){
            // TODO: EVENT_OPEN_TRANSFER_MODAL
            // this.$eventHub.listen(this.$eventHub.EVENT_OPEN_TRANSFER_MODAL, this.openModal);
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