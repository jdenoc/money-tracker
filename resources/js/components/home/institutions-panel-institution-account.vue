<template>
  <li class="institutions-panel-account py-1 px-3 cursor-pointer"
    v-bind:id="'account-'+id"
    v-bind:class="{'bg-blue-600 text-white is-active': isAccountFilterActive, 'bg-white hover:bg-gray-50 text-blue-400 hover:text-blue-500': !isAccountFilterActive}"
    v-tooltip="tooltipContent"
  >
    <div v-on:click="displayAccountEntries">
      <span class="institutions-panel-account-name" v-text="name"></span>
      <br/>
      <span v-show="isAccountTotalVisible" class="text-xs font-extrabold">
        <span class="account-currency mr-px" v-html="accountCurrencyHtml"></span>
        <span class="account-total" v-text="accountTotal"></span>
      </span>
    </div>
  </li>
</template>

<script lang="js">
import {Currency} from '../../currency';
// stores
import {useAccountTypesStore} from "../../stores/accountTypes";
import {usePaginationStore} from "../../stores/pagination";

export default {
  name: "institutions-panel-institution-account",
  props: {
    id: Number,
    name: String,
    total: Number,
    accountCurrency: String,
    canShowTooltip: {
      type: Boolean,
      default: true
    }
  },
  data: function(){
    return {}
  },
  computed: {
    accountTypesStore: function(){
      return useAccountTypesStore();
    },
    accountCurrencyHtml: function(){
      return this.currencyObject.getHtmlFromCode(this.accountCurrency);
    },
    accountTotal: function(){
      if(this.isAccountTotalVisible){
        return this.total.toFixed(2);
      } else {
        return '';
      }
    },
    accountTypeTooltipList: function(){
      let tooltipList = "";
      this.accountTypesStore.listActive
        .filter(function(accountType){
          return accountType.account_id === this.id;
        }.bind(this))
        .forEach(function(accountType){
          tooltipList += "&bull; "+accountType.name+" ("+accountType.last_digits+")<br/>"
        });
      return tooltipList.trim();
    },
    currencyObject: function(){
      return new Currency();
    },
    isAccountFilterActive: function(){
      let currentFilter = usePaginationStore().currentFilter;
      return Object.keys(currentFilter).length === 1
        && Object.prototype.hasOwnProperty.call(currentFilter, 'account')
        && currentFilter.account === this.id;
    },
    isAccountTotalVisible: function(){
      return !isNaN(this.total);
    },
    tooltipContent: function(){
      return this.canShowTooltip && {
        content: this.accountTypeTooltipList,
        html: true,
        placement: 'right',
        classes: 'text-xs font-semibold bg-black py-1.5 px-1 rounded rounded-lg text-white tooltip',
      }
    }
  },
  methods: {
    displayAccountEntries: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
      let filterDataParameters = {account: this.id};
      this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, {pageNumber: 0, filterParameters: filterDataParameters});
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../../../styles/tooltip";
</style>