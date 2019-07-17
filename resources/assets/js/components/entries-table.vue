<template>
    <div>
        <table id="entry-table" class="table is-narrow is-striped is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th></th>
                    <th>Date</th>
                    <th>Memo</th>
                    <th class="has-text-right">Income</th>
                    <th class="has-text-right">Expense</th>
                    <th>Type</th>
                    <th><i class="fas fa-paperclip"></i></th>
                    <th><i class="fas fa-exchange-alt"></i></th>
                    <th><i class="fas fa-tags"></i></th>
                </tr>
            </thead>
            <tbody>
                <!-- TODO: update entry transfers are a thing -->
                <entries-table-entry-row
                    v-for="entry in listOfEntries"
                    v-bind:key="entry.id"
                    v-bind:id="entry.id"
                    v-bind:date="entry.entry_date"
                    v-bind:accountTypeId="entry.account_type_id"
                    v-bind:value="entry.entry_value"
                    v-bind:memo="entry.memo"
                    v-bind:expense="entry.expense"
                    v-bind:confirm="entry.confirm"
                    v-bind:disabled="entry.disabled"
                    v-bind:hasAttachments="entry.has_attachments"
                    v-bind:isTransfer="entry.is_transfer"
                    v-bind:tagIds="entry.tags"
                ></entries-table-entry-row>
            </tbody>
        </table>
        <div id="pagination-buttons" class="level">
            <div class="level-left">
                <div class="level-item"><button id="paginate-btn-prev" class="button is-primary"
                    v-on:click="prevPage"
                    v-show="isPrevButtonVisible"
                >Previous</button></div>
            </div>
            <div class="level-right">
                <div class="level-item"><button id="paginate-btn-next" class="button is-primary"
                    v-on:click="nextPage"
                    v-show="isNextButtonVisible"
                >Next</button></div>
            </div>
        </div>
    </div>
</template>

<script>
    import {Entries} from '../entries';
    import EntriesTableEntryRow from "./entries-table-entry-row";
    import Store from '../store';

    export default {
        name: "entries-table",
        components: {EntriesTableEntryRow},
        data: function(){
            return {
                entries: new Entries(),
                pageMax: 50
            }
        },
        computed: {
            currentPage: function(){
                return Store.getters.currentPage;
            },
            currentFilter: function(){
                return Store.getters.currentFilter;
            },
            listOfEntries: function(){
                return this.entries.retrieve;
            },
            isNextButtonVisible: function(){
                return this.entries.count > this.pageMax && this.pageMax*(this.currentPage+1) < this.entries.count
            },
            isPrevButtonVisible: function(){
                return this.currentPage !== 0 && !_.isNull(this.currentPage);
            }
        },
        methods: {
            updateEntriesTableEventHandler: function(payload){
                if(_.isNull(payload)){
                    this.setPageNumber(0);
                }
                if(!_.isObject(payload)){
                    this.setPageNumber(payload);
                } else {
                    Store.dispatch('currentFilter', payload.filterParameters);
                    this.setPageNumber(payload.pageNumber);
                }
                this.updateEntriesTable(this.currentPage, this.currentFilter);
            },
            updateEntriesTable: function(pageNumber, filterParameters){
                this.entries.fetch(pageNumber, filterParameters)
                    .then(function(notification){
                        this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
                    }.bind(this))
                    .finally(function(){
                        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
                    }.bind(this));
            },
            nextPage: function(){
                this.setPageNumber(this.currentPage+1);
                this.updateEntriesTable(this.currentPage, this.currentFilter);
            },
            prevPage: function(){
                this.setPageNumber(this.currentPage-1);
                this.updateEntriesTable(this.currentPage, this.currentFilter);
            },
            setPageNumber: function(newPageNumber){
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
                Store.dispatch('currentPage', newPageNumber);
            }
        },
        created: function(){
            this.$eventHub.listen(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, this.updateEntriesTableEventHandler);
        }
    }
</script>

<style scoped>
    table#entry-table{
        margin-bottom: 0.25rem;
    }
    #pagination-buttons{
        padding: 0 7px 5px;
    }
    #pagination-buttons .button{
        width: 100px;
    }
</style>