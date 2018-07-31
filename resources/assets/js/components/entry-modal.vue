<template>
    <div class="modal" v-bind:class="{'is-active': isVisible}">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Entry: <span id="entry-id-display">new</span></p>
                <input type="hidden" name="entry-id" id="entry-id" />

                <div class="control">
                    <input class="is-checkradio is-block is-success" id="entry-confirm" type="checkbox" name="entry-confirm" v-model="entryData.confirmed" >
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
                            <select name="entry-account-type" id="entry-account-type">
                                <option></option>
                            </select>
                        </div></div>
                        <div class="help has-text-info" v-bind:class="{'is-hidden': !hasAccountTypeBeenSelected}">
                            <p>
                                <span class="has-text-weight-semibold has-padding-right">Account Name:</span>
                                <span id="entry-account-type-meta-account-name"></span>
                            </p>
                            <p>
                                <span class="has-text-weight-semibold has-padding-right">Last 4 Digits:</span>
                                <span class="entry-account-type-meta-last-digits"></span>
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

                <!--<a id="entry-tags-info" class="glyphicon glyphicon-info-sign text-info pull-right"></a>-->
                <div class="field is-horizontal">
                    <div class="field-label is-normal"><label class="label">Tags:</label></div>
                    <div class="field-body"><div class="field"><div class="control">
                        <voerro-tags-input
                            element-id="entry-tags"
                            v-model="tagsInput.selectedTags"
                            v-bind:existing-tags="tagsInput.autocompleteItems"
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
            let defaultData = {
                id: null,
                entry_date: "",
                account_type_id: null,
                entry_value: "",
                memo: "",
                expense: true,
                confirm: false,
                tags: [],
                attachments: []
            };
            return {
                tagsInput: {
                    // TODO: replace this with real data
                    // TODO: fill with data from tags.value when available
                    autocompleteItems: {
                        1: "tax",
                        2: "car",
                        3: "check",
                        4: "utilities",
                        5: "home",
                        6: "doctor",
                        7: "dentist",
                        8: "medication",
                        9: "vet",
                        10: "work",
                        11: "demo",
                        12: "door",
                        13: "dog",
                        14: "cat"
                    },
                    // TODO: make this mirror entryData.tags
                    selectedTags: [],
                },

                isVisible: true,
                isLocked: true,
                entryData: {
                    confirmed: false,
                    tags: []
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
                return this.entryData.confirmed;
            },
            isDeletable: function(){
                // TODO: true ONLY if an existing entry
                return true;
            },
            areAccountTypesSet: function(){
                // TODO: figure out how to set account-type values in entry modal
                // TODO: return true after account-type values are set
                return false;
            },
            hasAccountTypeBeenSelected: function(){
                // TODO: figure out how to change this value based on entry-account-type selection
                // TODO: only return true if account-type value != ''
                return true;
            },
            getAttachmentUploadUrl: function(){
                return this.dropzoneOptions.url;
            }
        },
        methods: {
            // TODO: toggle "locking" entry-modal
            // TODO: set all fields to read-only when entry-modal "locked"

            // TODO: init input tags auto-complete

            // TODO: update account-type help info when account-type is changed

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
                this.entryData.confirmed = false;
                this.isVisible = false;
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
            }
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
    #entry-account-type{
        min-width: 200px;
    }
    .has-padding-right{
        padding-right: 5px;
    }
</style>