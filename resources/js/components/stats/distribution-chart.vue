<template>
    <div id="stats-distribution">
        <section id="stats-form-distribution" class="pb-0 text-sm">
          <account-account-type-toggling-selector
              id="distribution-chart"
              class="max-w-lg mt-0 mx-4 mb-4"
              v-bind:account-or-account-type-id.sync="accountOrAccountTypeId"
              v-bind:account-or-account-type-toggled.sync="accountOrAccountTypeToggle"
          ></account-account-type-toggling-selector>

          <div class="max-w-lg mt-0 mx-4 mb-4">
            <toggle-button toggle-id="distribution-expense-or-income"
                           v-bind="toggleButtonProperties"
                           v-bind:toggle-state.sync="expenseOrIncomeToggle"
            ></toggle-button>
          </div>

          <date-range class="max-w-lg mt-0 mx-4 mb-4" chart-name="distribution-chart" v-bind:start-date.sync="startDate" v-bind:end-date.sync="endDate"></date-range>

          <div class="max-w-lg mt-0 mx-4 mb-4">
            <button class="generate-stats w-full py-2 text-white bg-blue-600 rounded opacity-90 hover:opacity-100" v-on:click="makeRequest">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-1.5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
              </svg>
              Generate Chart
            </button>
          </div>
        </section>

        <hr class="my-8"/>

        <section v-if="areEntriesAvailable && dataLoaded" class="stats-results-distribution pt-2">
            <include-transfers-checkbox
                chart-name="distribution"
                v-bind:include-transfers="includeTransfers"
                v-on:update-checkradio="includeTransfers = $event"
            ></include-transfers-checkbox>
            <pie-chart
                v-if="dataLoaded"
                v-bind:chart-data="chartData"
                v-bind:options="chartOptions"
            >Your browser does not support the canvas element.</pie-chart>
        </section>
        <section v-else class="text-center font-semibold text-base pt-0 overflow-auto stats-results-distribution">
            No data available
        </section>
    </div>
</template>

<script lang="js">
// utilities
import _ from 'lodash';
// components
import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
import DateRange from "./date-range";
import IncludeTransfersCheckbox from "./include-transfers-checkbox";
import PieChart from './chart-defaults/pie-chart';
import ToggleButton from "../toggle-button";
// mixins
import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
import {statsChartMixin} from "../../mixins/stats-chart-mixin";
import {tagsObjectMixin} from "../../mixins/tags-object-mixin";
import {tailwindColorsMixin} from "../../mixins/tailwind-colors-mixin";

    export default {
        name: "distribution-chart",
        mixins: [entriesObjectMixin, statsChartMixin, tagsObjectMixin, tailwindColorsMixin],
        components: {IncludeTransfersCheckbox, AccountAccountTypeTogglingSelector, DateRange, PieChart, ToggleButton},
        data: function(){
          return {
            accountOrAccountTypeId: '',
            accountOrAccountTypeToggle: true,
            endDate: '',
            expenseOrIncomeToggle: true,
            startDate: '',
          }
        },
        computed: {
            chartData: function(){
              let chartData = this.standardiseData;
              let chartBgColors = this.getRandomColors(chartData.length);

              return {
                  labels: chartData.map(function(d){ return d.x }),
                  datasets: [{
                      data: chartData.map(function(d){ return d.y }),
                      backgroundColor: chartBgColors
                  }]
              };
            },
            chartOptions: function(){
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    title: {
                        display: true,
                        text: this.chartConfig.titleText
                    },
                }
            },
            standardiseData: function(){
                let standardisedChartData = [];

                this.largeBatchEntryData
                    .filter(this.filterIncludeTransferEntries)
                    .forEach(function(entryDatum){
                        let tempDatum = _.cloneDeep(entryDatum);
                        if(tempDatum.tags.length === 0){
                            tempDatum.tags.push(0);
                        }
                        tempDatum.tags.forEach(function(tag){
                            let key = (tag === 0) ? 'untagged' : this.tagsObject.getNameById(tag);
                            if(!standardisedChartData.hasOwnProperty(key)){
                                standardisedChartData[key] = {x: key, y: 0}
                            }
                            standardisedChartData[key].y += parseFloat(tempDatum.entry_value);
                            standardisedChartData[key].y = _.round(standardisedChartData[key].y, 2);
                        }.bind(this));
                    }.bind(this), Object.create(null));

                return _.sortBy(Object.values(standardisedChartData), function(o){ return o.x;});
            },
            toggleButtonProperties: function(){
              return {
                colorChecked: this.tailwindColors.gray[400],
                colorUnchecked: this.tailwindColors.gray[400],
                fontSize: 16,  // px
                labelChecked: "Expense",
                labelUnchecked: "Income",
                height: 40, // px
                width: 512 // px  // tailwind class: .max-w-lg
              };
            },
        },
        methods: {
          setChartTitle: function(isExpense, startDate, endDate){
            this.chartConfig.titleText = (isExpense ? "Expense" : "Income")+" Distribution ["+startDate+" - "+endDate+"]";
          },

          makeRequest: function(){
            this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

            let chartDataFilterParameters = {
              start_date: this.startDate,
              end_date: this.endDate,
            };

            chartDataFilterParameters.expense = this.expenseOrIncomeToggle;

            if(this.accountOrAccountTypeToggle === true){
              chartDataFilterParameters.account = this.accountOrAccountTypeId;
            } else {
              chartDataFilterParameters.account_type = this.accountOrAccountTypeId;
            }

            this.setChartTitle(chartDataFilterParameters.expense, chartDataFilterParameters.start_date, chartDataFilterParameters.end_date);
            this.multiPageDataFetch(chartDataFilterParameters);
          }
        }
    }
</script>

<style lang="scss" scoped>
    // #account-or-account-type-toggling-selector-for-distribution-chart obtained from the account-account-type-toggling-selector component
    ::v-deep #account-or-account-type-toggling-selector-for-distribution-chart select{
      // tailwind class .w-full
      width: 100%;
    }
</style>