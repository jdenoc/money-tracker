<template>
  <div>
    <table id="entry-table" class="mb-1 w-full table-auto scroll-mt-16">
      <thead>
      <tr>
        <th></th>
        <th class="text-left px-1">Date</th>
        <th class="text-left px-1">Memo</th>
        <th class="text-right">Income</th>
        <th class="text-right">Expense</th>
        <th class="text-left px-2">Type</th>
        <th>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
          </svg>
        </th>
        <th>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
          </svg>
        </th>
        <th class="text-left">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
          </svg>
        </th>
      </tr>
      </thead>
      <tbody>
      <entries-table-entry-row
          v-for="entry in entriesStore.collection"
          v-bind:key="entry.id"
          v-bind="generateEntryRowOptions(entry)"
      ></entries-table-entry-row>
      </tbody>
    </table>

    <div id="pagination-buttons" class="mx-5 mt-3">
      <button id="paginate-btn-prev" class="w-24 rounded py-2 px-4 float-left bg-blue-100 hover:bg-blue-200"
              v-on:click="prevPage"
              v-show="isPrevButtonVisible"
      >Previous</button>
      <button id="paginate-btn-next" class="w-24 rounded py-2 px-4 float-right bg-blue-100 hover:bg-blue-200"
              v-on:click="nextPage"
              v-show="isNextButtonVisible"
      >Next</button>
    </div>
  </div>
</template>

<script lang="js">
import _ from 'lodash';
// components
import EntriesTableEntryRow from "./entries-table-entry-row";
// stores
import {useEntriesStore} from "../../stores/entries";
import {usePaginationStore} from "../../stores/pagination";

export default {
  name: "entries-table",
  components: {EntriesTableEntryRow},
  data: function(){
    return { }
  },
  computed: {
    currentFilter: function(){
      return usePaginationStore().currentFilter;
    },
    currentPage: function() {
      return usePaginationStore().currentPage;
    },
    entriesStore: function(){
      return useEntriesStore();
    },
    entryCountMax: function(){
      return 50;
    },
    isNextButtonVisible: function(){
      return this.entriesStore.totalCount > this.entryCountMax
        && this.entryCountMax*(this.currentPage+1) < this.entriesStore.totalCount
    },
    isPrevButtonVisible: function(){
      return this.currentPage !== 0 && !_.isNull(this.currentPage);
    },
  },
  methods: {
    generateEntryRowOptions: function(entry) {
      let hasAttachments;
      if (typeof entry.has_attachments !== 'undefined') {
        hasAttachments = entry.has_attachments;
      } else {
        hasAttachments = !_.isEmpty(entry.attachments);
      }
      let isTransfer;
      if(typeof entry.is_transfer !== 'undefined'){
        isTransfer = entry.is_transfer;
      } else {
        isTransfer = !_.isNull(entry.transfer_entry_id);
      }
      return {
        id: entry.id,
        date: entry.entry_date,
        accountTypeId: entry.account_type_id,
        value: entry.entry_value,
        memo: entry.memo,
        expense: entry.expense,
        confirm: entry.confirm,
        hasAttachments: hasAttachments,
        isTransfer: isTransfer,
        tagIds: entry.tags
      };
    },
    nextPage: function(){
      this.setPageNumber(this.currentPage+1);
      this.updateEntriesTable(this.currentPage, this.currentFilter)
        .finally(this.scrollTableTopIntoView);
    },
    prevPage: function(){
      this.setPageNumber(this.currentPage-1);
      this.updateEntriesTable(this.currentPage, this.currentFilter)
        .finally(this.scrollPaginationButtonsIntoView);
    },
    scrollPaginationButtonsIntoView(){
      document.querySelector('#pagination-buttons').scrollIntoView();
    },
    scrollTableTopIntoView(){
      document.querySelector('#entry-table').scrollIntoView();
    },
    setPageNumber: function(newPageNumber){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      usePaginationStore().currentPage = newPageNumber;
    },
    updateEntriesTable: function(pageNumber, filterParameters){
      return this.entriesStore.fetch(pageNumber, filterParameters)
        .then(function(notification){
          this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
        }.bind(this))
        .finally(function(){
          this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
        }.bind(this));
    },
    updateEntriesTableEventHandler: function(payload){
      if(_.isNull(payload)){
        this.setPageNumber(0);
      }
      if(!_.isObject(payload)){
        this.setPageNumber(payload);
      } else {
        usePaginationStore().currentFilter = payload.filterParameters;
        this.setPageNumber(payload.pageNumber);
      }
      this.updateEntriesTable(this.currentPage, this.currentFilter)
        .finally(this.scrollTableTopIntoView);
    },
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, this.updateEntriesTableEventHandler);
  }
}
</script>

<style scoped>
</style>