<template>
    <div id="stats-trending">
        <section id="stats-form-trending" class="pb-0 text-sm">
          <account-account-type-toggling-selector
                id="trending-chart"
                class="max-w-lg mt-0 mx-4 mb-4"
                v-bind:account-or-account-type-id.sync="accountOrAccountTypeId"
                v-bind:account-or-account-type-toggled.sync="accountOrAccountTypeToggle"
          ></account-account-type-toggling-selector>

          <date-range class="max-w-lg mt-0 mx-4 mb-4" chart-name="trending-chart" v-bind:start-date.sync="startDate" v-bind:end-date.sync="endDate"></date-range>

          <div class="max-w-lg mt-0 mx-4 mb-4">
            <button class="generate-stats w-full py-2 text-white bg-blue-600 rounded opacity-90 hover:opacity-100" v-on:click="displayData">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-1.5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
              </svg>
              Generate Chart
            </button>
          </div>
        </section>

        <hr class="my-8" />

        <section v-if="areEntriesAvailable && dataLoaded" class="stats-results-trending pt-2">
            <include-transfers-checkbox
                chart-name="trending"
                v-bind:include-transfers="includeTransfers"
                v-on:update-checkradio="includeTransfers = $event"
            ></include-transfers-checkbox>
            <line-chart
                v-if="dataLoaded"
                v-bind:chart-data="chartData"
                v-bind:options="chartOptions"
            >Your browser does not support the canvas element.</line-chart>
        </section>
        <section v-else class="text-center font-semibold text-base stats-results-trending pt-0 overflow-auto">
            No data available
        </section>
    </div>
</template>

<script>
// utilities
import _ from 'lodash';
// components
import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
import DateRange from "./date-range";
import IncludeTransfersCheckbox from "./include-transfers-checkbox";
import LineChart from "./chart-defaults/line-chart";
// mixins
import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
import {statsChartMixin} from "../../mixins/stats-chart-mixin";
import {tailwindColorsMixin} from "../../mixins/tailwind-colors-mixin";

export default {
  name: "trending-chart",
  mixins: [entriesObjectMixin, statsChartMixin, tailwindColorsMixin],
  components: {AccountAccountTypeTogglingSelector, DateRange, IncludeTransfersCheckbox, LineChart},
  data: function(){
    return {
      accountOrAccountTypeId: '',
      accountOrAccountTypeToggle: true,
      chartConfig: {
        colors: {
          blue: '',
          green: '',
          purple: '',
          red: '',
        },
        datasetDefault: {
          fill: false
        },
        timeUnit: 'day'
      },
      endDate: '',
      startDate: '',
    }
  },
  computed: {
    millisecondsPerDay: function(){
      return 1000*3600*24;
    },
    incomeData: function(){
      return this.standardiseData(false);
    },
    expenseData: function(){
      return this.standardiseData(true);
    },
    comparisonData: function(){
      let comparisonChartData = [];
      this.expenseData
        .forEach(function(datum){
          let key = datum.x;
          if(!Object.prototype.hasOwnProperty.call(comparisonChartData, key)){
            comparisonChartData[key] = {x: key, y: 0}
          }
          comparisonChartData[key].y -= parseFloat(datum.y);
          comparisonChartData[key].y = _.round(comparisonChartData[key].y, 2);
        }.bind(this));
      this.incomeData
        .forEach(function(datum){
          let key = datum.x;
          if(!Object.prototype.hasOwnProperty.call(comparisonChartData, key)){
            comparisonChartData[key] = {x: key, y: 0}
          }
          comparisonChartData[key].y += parseFloat(datum.y);
          comparisonChartData[key].y = _.round(comparisonChartData[key].y, 2);
        }.bind(this));

      return _.sortBy(
        Object.values(comparisonChartData),
        function(o){ return o.x;}
      );
    },
    periodTotalsData: function(){
      let periodTotalData = [];
      this.comparisonData
        .reduce(function(previousValue, currentObject, index){
          periodTotalData[index] = {x: currentObject.x, y: _.round(previousValue+currentObject.y, 2)};
          return periodTotalData[index].y;
        }, 0);
      return periodTotalData;
    },

    chartData: function(){
      let incomeDataset = _.merge(
        {
          data: this.incomeData,
          label: "income",
          backgroundColor: this.chartConfig.colors.blue,
          borderColor: this.chartConfig.colors.blue
        },
        this.chartConfig.datasetDefault
      );
      let expenseDataset = _.merge(
        {
          data: this.expenseData,
          label: "expense",
          backgroundColor: this.chartConfig.colors.red,
          borderColor: this.chartConfig.colors.red
        },
        this.chartConfig.datasetDefault
      );
      let comparisonDataset = _.merge(
        {
          data: this.comparisonData,
          label: 'comparison',
          backgroundColor: this.chartConfig.colors.purple,
          borderColor: this.chartConfig.colors.purple
        },
        this.chartConfig.datasetDefault
      );
      let periodTotalDataset = _.merge(
        {
          data: this.periodTotalsData,
          label:"period total",
          backgroundColor: this.chartConfig.colors.green,
          borderColor: this.chartConfig.colors.green
        },
        this.chartConfig.datasetDefault
      );

      return {
        datasets: [
          incomeDataset,
          expenseDataset,
          comparisonDataset,
          periodTotalDataset,
        ],
        legend: {
          display: true
        }
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
        scales: {
          xAxes: [{
            display: true,
            scaleLabel: {
              display: true,
              labelString: 'entry date'
            },
            type: 'time',
            time: {
              unit: this.chartConfig.timeUnit,
            },
            ticks: {
              autoSkip: true,
              maxRotation: 90,
              minRotation: 90
            }
          }],
        }
      };
    },

    getBulmaCalendar: function(){
      return this.$refs.trendingStatsChartBulmaCalendar;
    }
  },
  methods: {
    standardiseData: function(isExpense){
      let standardisedChartData = [];
      this.largeBatchEntryData
        .filter(this.filterIncludeTransferEntries)
        .filter(function(chartDatum){ return chartDatum.expense === isExpense })
        .forEach(function(datum){
          // condense data points with similar entry_date values
          let key = datum.entry_date;
          if(!Object.prototype.hasOwnProperty.call(standardisedChartData, key)){
            standardisedChartData[key] = {x: key, y: 0}
          }
          standardisedChartData[key].y += parseFloat(datum.entry_value);
          standardisedChartData[key].y = _.round(standardisedChartData[key].y, 2);
        }.bind(this));

      return _.sortBy(
        Object.values(standardisedChartData),
        function(o){ return o.x;}
      );
    },

    setChartTitle: function(startDate, endDate){
      this.chartConfig.titleText = "Trending ["+startDate+" - "+endDate+"]";
    },

    setChartTimeUnit: function(startDate, endDate){
      let s1 = new Date(startDate).getTime();
      let s2 = new Date(endDate).getTime();
      let dayDiff = (s2-s1)/this.millisecondsPerDay; // milliseconds per day

      if(dayDiff <= 30){  // <= 1 month:  day
        this.chartConfig.timeUnit = 'day';
      } else if(dayDiff <= 3*30){  // <= 3 months:  week
        this.chartConfig.timeUnit = 'week';
      } else  if(dayDiff >= 365*3){  // >= 3 years:  year
        this.chartConfig.timeUnit = 'year';
      } else {  // otherwise:  month
        this.chartConfig.timeUnit = 'month';
      }
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

      this.setChartTitle(chartDataFilterParameters.start_date, chartDataFilterParameters.end_date);
      this.setChartTimeUnit(chartDataFilterParameters.start_date, chartDataFilterParameters.end_date);
      this.multiPageDataFetch(chartDataFilterParameters);
    },
  },
  mounted: function(){
    this.chartConfig.colors.blue = this.tailwindColors.sky[600];
    this.chartConfig.colors.green = this.tailwindColors.emerald[500];
    this.chartConfig.colors.purple = this.tailwindColors.purple[700];
    this.chartConfig.colors.red = this.tailwindColors.red[600];
  }
}
</script>

<style lang="scss" scoped>
// #account-or-account-type-toggling-selector-for-trending-chart obtained
// from the account-account-type-toggling-selector component
::v-deep #account-or-account-type-toggling-selector-for-trending-chart select {
  // tailwind class .w-full
  width: 100%;
}
</style>