<template>
    <div id="stats-summary">
        <section id="stats-form-summary" class="section">
            <account-account-type-toggling-selector
                id="summary-chart"
                v-bind:account-or-account-type-id="accountOrAccountTypeId"
                v-bind:account-or-account-type-toggled="accountOrAccountTypeToggle"
                v-on:update-select="accountOrAccountTypeId = $event"
                v-on:update-toggle="accountOrAccountTypeToggle = $event"
            ></account-account-type-toggling-selector>

            <div class="field">
                <bulma-calendar
                    ref="summaryStatsChartBulmaCalendar"
                ></bulma-calendar>
            </div>

            <div class="field"><div class="control">
                <button class="button is-primary generate-stats" v-on:click="displayData"><i class="fas fa-table"></i>Generate Tables</button>
            </div></div>
        </section>
        <hr />

        <section class="section stats-results-summary" v-if="areEntriesAvailable">
            <table class="table">
                <caption class="subtitle is-5 has-text-left">Total Income/Expenses</caption>
                <thead>
                    <tr>
                        <th>Income</th>
                        <th>Expense</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(total, currencyIsoCode) in totalIncomeOrExpense">
                        <td class="has-text-right">
                            <i v-bind:class="currencyCssClass(currencyIsoCode)"></i>
                            <span v-text="total.income.toFixed(2)"></span>
                        </td>
                        <td class="has-text-right">
                            <i v-bind:class="currencyCssClass(currencyIsoCode)"></i>
                            <span v-text="total.expense.toFixed(2)"></span>
                        </td>
                        <td v-text="currencyIsoCode"></td>
                    </tr>
                </tbody>
            </table>

            <hr/>

            <table class="table">
                <caption class="subtitle is-5 has-text-left">Top 10 income/expense entries</caption>
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th colspan="2">Income</th>
                        <th colspan="2">Expense</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(incomeAndExpense, index) in top10IncomeAndExpenses" v-bind:key="index">
                        <td v-text="index+1"></td>
                        <td v-text="incomeAndExpense.incomeMemo"></td>
                        <td v-text="incomeAndExpense.incomeValue" class="has-text-right"></td>
                        <td v-text="incomeAndExpense.expenseMemo"></td>
                        <td v-text="incomeAndExpense.expenseValue" class="has-text-right"></td>
                    </tr>
                </tbody>
            </table>
        </section>
        <section v-else class="section has-text-centered has-text-weight-semibold is-size-6 stats-results-summary">
            No data available
        </section>
    </div>
</template>

<script>
    import bulmaCalendar from '../bulma-calendar';
    import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
    import {Currency} from "../../currency";

    import {accountsObjectMixin} from "../../mixins/accounts-object-mixin";
    import {accountTypesObjectMixin} from "../../mixins/account-types-object-mixin";
    import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
    import {statsChartMixin} from "../../mixins/stats-chart-mixin";

    export default {
        name: "summary-chart",
        mixins: [statsChartMixin, entriesObjectMixin, accountsObjectMixin, accountTypesObjectMixin],
        components: {AccountAccountTypeTogglingSelector, bulmaCalendar},
        data: function(){
          return {
              currencyObject: new Currency(),

              accountOrAccountTypeToggle: true,
              accountOrAccountTypeId: '',
          }
        },
        computed: {
            getBulmaCalendar: function(){
                return this.$refs.summaryStatsChartBulmaCalendar;
            },

            top10IncomeAndExpenses: function(){
                let incomeEntries = this.filteredEntries(false).sort(function(a, b){
                    // largest to smallest
                    return b.entry_value - a.entry_value;
                });
                let expenseEntries = this.filteredEntries(true).sort(function(a, b){
                    // largest to smallest
                    return b.entry_value - a.entry_value;
                });

                let topEntries = [];
                for(let i=0; i< 10; i++){
                    if(incomeEntries[i] === undefined && expenseEntries[i] === undefined){
                        break;
                    }
                    let incomeValue = incomeEntries[i] ? parseFloat(incomeEntries[i].entry_value).toFixed(2) : '';
                    let expenseValue = expenseEntries[i] ? parseFloat(expenseEntries[i].entry_value).toFixed(2) : '';
                    topEntries.push({
                        incomeMemo: incomeEntries[i] ? incomeEntries[i].memo : '',
                        incomeValue: incomeValue,
                        expenseMemo: expenseEntries[i] ? expenseEntries[i].memo : '',
                        expenseValue: expenseValue
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
                this.largeBatchEntryData.forEach(function(datum){
                    let accountCurrency = this.getAccountCurrencyFromAccountTypeId(datum.account_type_id);
                    if(datum.expense){
                        total[accountCurrency].expense += parseFloat(datum.entry_value);
                    } else {
                        total[accountCurrency].income += parseFloat(datum.entry_value);
                    }
                }.bind(this));

                // prune empty currencies
                Object.keys(total).map(function(currency, index) {
                    if(total[currency].income === 0 && total[currency].expense === 0){
                        delete total[currency];
                    }
                });

                return total;
            },
        },
        methods: {
            filteredEntries: function(isExpense){
                // return this.rawEntriesData.filter(function(datum){ return datum.expense === isExpense; });
                return this.largeBatchEntryData.filter(function(datum){ return datum.expense === isExpense; });
            },

            getAccountCurrencyFromAccountTypeId: function(accountTypeId){
                let account = this.accountTypesObject.getAccount(accountTypeId);
                return account.currency;
            },

            displayData: function(){
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

                let chartDataFilterParameters = {
                    start_date: this.getBulmaCalendar.calendarStartDate(),
                    end_date: this.getBulmaCalendar.calendarEndDate(),
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

            currencyCssClass: function(currencyISOCode){
                return this.currencyObject.getClassFromCode(currencyISOCode);
            }
        },
        mounted: function(){
            this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentMonthStartDate, this.currentMonthEndDate);
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../../sass/stats-chart';
</style>