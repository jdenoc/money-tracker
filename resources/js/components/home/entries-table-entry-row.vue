<template>
  <tr class="border bg-white hover:bg-gray-50 scroll-mt-16"
      v-bind:id="'entry-'+id"
      v-bind:class="{
        'text-gray-500': isFutureEntry,
        'unconfirmed bg-yellow-50 hover:bg-yellow-75': !confirm,
        'is-confirmed': confirm,
        'is-expense': expense,
        'is-income': !expense,
        'bg-green-50 hover:bg-green-75': confirm && !expense,
        'has-attachments': hasAttachments,
        'is-transfer': isTransfer,
        'has-tags': tagIds.length > 0
      }"
  >
    <td class="align-top py-1 pl-2"><button class="edit-entry-button text-blue-600 py-2 px-2 my-0.5 rounded-md bg-white hover:bg-gray-100" v-on:click="openEditEntryModal(id)">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-0.5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
      </svg>
    </button></td>
    <td class="row-entry-date align-top px-1 py-1 " v-text="date"></td>
    <td class="row-entry-memo align-top px-1 py-1 " v-text="memo"></td>
    <td class="text-right align-top py-1" v-bind:class="{'row-entry-value': !expense}">
      <span v-if="expense"></span>
      <span v-else>{{value}}</span>
    </td>
    <td class="text-right align-top py-1 " v-bind:class="{'row-entry-value': expense}">
      <span v-if="expense">{{value}}</span>
      <span v-else></span>
    </td>
    <td class="row-entry-account-type align-top px-2 py-1 " v-text="accountTypeName"></td>
    <td class="row-entry-attachment align-top py-1">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-show="hasAttachments">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
    </td>
    <td class="row-entry-transfer align-top py-1">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-show="isTransfer">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
    </td>
    <td class="row-entry-tags align-top py-1 "><div class="tags flex flex-wrap">
      <span class="tag rounded-full bg-gray-800 text-gray-300 px-2 py-1 text-xs mx-1 mb-1" v-for="tagId in tagIds" v-bind:key="tagId" v-text="getTagName(tagId)"></span>
    </div></td>
  </tr>
</template>

<script lang="js">
import {AccountTypes} from "../../account-types";
import {Tags} from "../../tags";
// import _ from "lodash";

export default {
  name: "entries-table-entry-row",
  props: {
    id: {type: Number, required: true},
    date: {type: String},
    accountTypeId: {type: Number},
    value: {type: String},
    memo: {type: String},
    expense: {type: Boolean},
    confirm: {type: Boolean},
    disabled: {type: Boolean, default: false},
    hasAttachments: {type: Boolean, default: false},
    isTransfer: {type: Boolean, default: false},
    tagIds: {type: Array, default: function(){ return []; }}
  },
  computed: {
    accountTypeName: function(){
      return new AccountTypes().getNameById(this.accountTypeId);
    },
    isFutureEntry: function(){
      let timezoneOffset = new Date().getTimezoneOffset()*this.millisecondsPerMinute;
      return Date.parse(this.date)+timezoneOffset > Date.now();
    },
    millisecondsPerMinute: function(){
      return 60000;
    }
  },
  methods: {
    getTagName: function(tagId){
      return new Tags().getNameById(tagId);
      // if(_.isNumber(tag)) {
      //   return new Tags().getNameById(tag);
      // } else if(_.isObject(tag) && tag.name){
      //   return tag.name;
      // } else {
      //   // no idea what this is...
      //   console.warn('invalid tag provided while displaying tags in entry-table:'+JSON.stringify(tag));
      //   return '';
      // }
    },
    openEditEntryModal: function(entryId){
      this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_MODAL_OPEN, entryId);
    },
  }
}
</script>

<style lang="scss" scoped>
.tags {
  max-width: 16rem;
}
</style>