<template>
    <div id="stats-summary">
        <section id="stats-form-summary" class="pb-0 text-sm">
            <account-account-type-toggling-selector
                id="summary-chart"
                class="max-w-lg mt-0 mx-4 mb-4"
                v-bind:account-or-account-type-id.sync="accountOrAccountTypeId"
                v-bind:account-or-account-type-toggled.sync="accountOrAccountTypeToggle"
            ></account-account-type-toggling-selector>

            <date-range class="max-w-lg mt-0 mx-4 mb-4" chart-name="summary-chart" v-bind:start-date.sync="startDate" v-bind:end-date.sync="endDate"></date-range>

            <div class="max-w-lg mt-0 mx-4 mb-4">
              <button class="generate-stats w-full py-2 text-white bg-blue-600 rounded opacity-90 hover:opacity-100" v-on:click="displayData">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-1.5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Generate Tables
              </button>
            </div>
        </section>

        <hr class="my-8"/>

        <section class="stats-results-summary pt-2" v-if="areEntriesAvailable && dataLoaded">
            <include-transfers-checkbox
                chart-name="summary"
                v-bind:include-transfers="includeTransfers"
                v-on:update-checkradio="includeTransfers = $event"
            ></include-transfers-checkbox>

            <table class="table-auto">
                <caption class="text-lg mb-3 text-left">Total Income/Expenses</caption>
                <thead class="border-b-2 border-b-gray-300">
                    <tr>
                        <th class="py-1 px-4">Income</th>
                        <th class="py-1 px-4">Expense</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(total, currencyIsoCode) in totalIncomeOrExpense" v-bind:key="currencyIsoCode">
                        <td class="text-right py-1 px-4">
                          <span v-html="currencyHtml(currencyIsoCode)"></span>
                          <span v-text="total.income.toFixed(2)"></span>
                        </td>
                        <td class="text-right py-1 px-4">
                          <span v-html="currencyHtml(currencyIsoCode)"></span>
                          <span v-text="total.expense.toFixed(2)"></span>
                        </td>
                        <td v-text="currencyIsoCode"></td>
                    </tr>
                </tbody>
            </table>

            <hr class="my-6"/>

            <table class="table-auto border-gray-300">
                <caption class="text-lg mb-3 text-left">Top 10 income/expense entries</caption>
                <thead class="border-b-2">
                    <tr>
                        <th>&nbsp;</th>
                        <th colspan="2" class="text-left py-1 px-2">Income</th>
                        <th colspan="2" class="text-left border-l px-2 py-1">Expense</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(incomeAndExpense, index) in top10IncomeAndExpenses" v-bind:key="index" class="border-t hover:bg-gray-100">
                        <td v-text="index+1" class="py-1 pl-2 pr-3 text-right"></td>
                        <td v-text="incomeAndExpense.incomeMemo" v-tooltip="tooltipContent(incomeAndExpense.incomeDate)" class="py-1 px-2"></td>
                        <td v-text="incomeAndExpense.incomeValue" class="text-right py-1 pl-4 pr-2"></td>
                        <td v-text="incomeAndExpense.expenseMemo" class="border-l border-l-gray-300 py-1 px-2" v-tooltip="tooltipContent(incomeAndExpense.expenseDate)"></td>
                        <td v-text="incomeAndExpense.expenseValue" class="text-right py-1 pl-4 pr-2"></td>
                    </tr>
                </tbody>
            </table>
        </section>
        <section v-else class="text-center font-semibold text-base stats-results-summary pt-0 overflow-auto">
            No data available
        </section>
    </div>
</template>

<script>
// components
import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
import DateRange from "./date-range";
import IncludeTransfersCheckbox from "./include-transfers-checkbox";
// utilities
import _ from 'lodash';
import {Currency} from "../../currency";
// mixins
import {accountsObjectMixin} from "../../mixins/accounts-object-mixin";
import {accountTypesObjectMixin} from "../../mixins/account-types-object-mixin";
import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
import {statsChartMixin} from "../../mixins/stats-chart-mixin";

export default {
  name: "summary-chart",
  mixins: [statsChartMixin, entriesObjectMixin, accountsObjectMixin, accountTypesObjectMixin],
  components: {DateRange, IncludeTransfersCheckbox, AccountAccountTypeTogglingSelector},
  data: function(){
    return {
      accountOrAccountTypeId: '',
      accountOrAccountTypeToggle: true,
      currencyObject: new Currency(),
      endDate: '',
      startDate: '',
    }
  },
  computed: {
    top10IncomeAndExpenses: function(){
      let incomeEntries = _.orderBy(
        this.filteredEntries(false),
        ['entry_value', 'entry_date', 'id'],
        ['desc', 'desc', 'desc']
      );

      let expenseEntries = _.orderBy(
        this.filteredEntries(true),
        ['entry_value', 'entry_date', 'id'],
        ['desc', 'desc', 'desc']
      );

      let topEntries = [];
      for(let i=0; i< 10; i++){
        if(incomeEntries[i] === undefined && expenseEntries[i] === undefined){
          break;
        }
        topEntries.push({
          incomeMemo: incomeEntries[i] ? incomeEntries[i].memo : '',
          incomeValue: incomeEntries[i] ? parseFloat(incomeEntries[i].entry_value).toFixed(2) : '',
          incomeDate: incomeEntries[i] ? incomeEntries[i].entry_date : '',
          expenseMemo: expenseEntries[i] ? expenseEntries[i].memo : '',
          expenseValue: expenseEntries[i] ? parseFloat(expenseEntries[i].entry_value).toFixed(2) : '',
          expenseDate: expenseEntries[i] ? expenseEntries[i].entry_date : '',
        });
      }
      return topEntries;
    },

    totalIncomeOrExpense: function(){
      let total = {};
      // init total
      this.rawAccountsData
        .forEach(function(account){
          if(total[account.currency] === undefined){
            total[account.currency] = {};
          }
          total[account.currency].income = 0;
          total[account.currency].expense = 0;
        });

      // tally up values for total
      this.largeBatchEntryData
        .filter(this.filterIncludeTransferEntries)
        .forEach(function(datum){
          let accountCurrency = this.getAccountCurrencyFromAccountTypeId(datum.account_type_id);
          if(datum.expense){
            total[accountCurrency].expense += parseFloat(datum.entry_value);
          } else {
            total[accountCurrency].income += parseFloat(datum.entry_value);
          }
        }.bind(this));

      // prune empty currencies
      Object.keys(total).map(function(currency) {
        if(total[currency].income === 0 && total[currency].expense === 0){
          delete total[currency];
        }
      });

      return total;
    },
  },
  methods: {
    tooltipContent: function(text){
      return {
        content: text,
        html: true,
        placement: 'right',
        classes: 'text-xs font-semibold bg-black py-1.5 px-1 rounded rounded-lg text-white tooltip',
      }
    },
    filteredEntries: function(isExpense){
      return this.largeBatchEntryData
        .filter(this.filterIncludeTransferEntries)
        .filter(function(datum){ return datum.expense === isExpense; })
        .map(function(entry){
          let e = _.clone(entry);
          e.entry_value = _.round(entry.entry_value, 2);
          return e;
        });
    },

    getAccountCurrencyFromAccountTypeId: function(accountTypeId){
      let account = this.accountTypesObject.getAccount(accountTypeId);
      return account.currency;
    },

    displayData: function(){
      this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

      let chartDataFilterParameters = {
        start_date: this.startDate,
        end_date: this.endDate,
      };

      if(this.accountOrAccountTypeToggle === true){
        chartDataFilterParameters.account = this.accountOrAccountTypeId;
      } else {
        chartDataFilterParameters.account_type = this.accountOrAccountTypeId;
      }

      this.multiPageDataFetch(chartDataFilterParameters);
    },

    resetAccountTypesSelector: function(){
      this.summaryAccountTypeId = null;
    },

    currencyHtml: function(currencyISOCode){
      return this.currencyObject.getHtmlFromCode(currencyISOCode);
    },
  }
}
</script>

<style lang="scss" scoped>
@import "../../../styles/tooltip";

table:nth-child(3) th:first-child {
  width: 2%;
}

table:nth-child(3) th:nth-child(2),
table:nth-child(3) th:nth-child(3) {
  width: 48%;
}

// #account-or-account-type-toggling-selector-for-summary-chart obtained
// from the account-account-type-toggling-selector component
::v-deep #account-or-account-type-toggling-selector-for-summary-chart select {
  // tailwind class .w-full
  width: 100%;
}
</style>