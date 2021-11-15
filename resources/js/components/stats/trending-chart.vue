<template>
    <div id="stats-trending">
        <section id="stats-form-trending" class="section">
            <account-account-type-toggling-selector
                id="trending-chart"
                v-model:account-or-account-type-id="accountOrAccountTypeId"
                v-model:account-or-account-type-toggled="accountOrAccountTypeToggle"
            ></account-account-type-toggling-selector>

            <div class="field">
                <bulma-calendar
                    ref="trendingStatsChartBulmaCalendar"
                ></bulma-calendar>
            </div>

            <div class="field"><div class="control">
                <button class="button is-primary generate-stats" v-on:click="displayData"><i class="fas fa-chart-area"></i>Generate Chart</button>
            </div></div>
        </section>

        <hr />

        <section v-if="areEntriesAvailable && dataLoaded" class="section stats-results-trending">
            <include-transfers-checkbox
                chart-name="trending"
                v-model:include-transfers="includeTransfers"
            ></include-transfers-checkbox>
            <line-chart
                v-if="dataLoaded"
                v-bind:chart-data="chartData"
                v-bind:chart-options="chartOptions"
            >Your browser does not support the canvas element.</line-chart>
        </section>
        <section v-else class="section has-text-centered has-text-weight-semibold is-size-6 stats-results-trending">
            No data available
        </section>
    </div>
</template>

<script>
    // utilities
    import _ from 'lodash';
    import 'chartjs-adapter-moment';  // required for options.scales.time & options.scales.ticks
    // components
    import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
    import BulmaCalendar from "../bulma-calendar";
    import IncludeTransfersCheckbox from "../include-transfers-checkbox";
    import LineChart from "./chart-defaults/line-chart";
    // mixins
    import {bulmaColorsMixin} from "../../mixins/bulma-colors-mixin";
    import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
    import {statsChartFormMixin} from "../../mixins/stats-chart-form-mixin";

    export default {
        name: "trending-chart",
        mixins: [entriesObjectMixin, statsChartFormMixin, bulmaColorsMixin],
        components: {IncludeTransfersCheckbox, BulmaCalendar, LineChart, AccountAccountTypeTogglingSelector},
        data: function(){
          return {
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

            accountOrAccountTypeToggle: true,
            accountOrAccountTypeId: null,
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
                if(!comparisonChartData.hasOwnProperty(key)){
                  comparisonChartData[key] = {x: key, y: 0}
                }
                comparisonChartData[key].y -= parseFloat(datum.y);
                comparisonChartData[key].y = _.round(comparisonChartData[key].y, 2);
              }.bind(this));
            this.incomeData
              .forEach(function(datum){
                let key = datum.x;
                if(!comparisonChartData.hasOwnProperty(key)){
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
              plugins: {
                title: {
                  display: true,
                  text: this.chartConfig.titleText
                },
              },
              scales: {
                x: {
                  type: 'time',
                  title: {
                    labelString: 'entry date'
                  },
                  time: {
                    unit: this.chartConfig.timeUnit,
                  },
                  ticks: {
                    autoSkip: true,
                    maxRotation: 90,
                    minRotation: 90
                  }
                }
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
                        if(!standardisedChartData.hasOwnProperty(key)){
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
                this.$eventBus.broadcast(this.$eventBus.EVENT_LOADING_SHOW());

                let chartDataFilterParameters = {
                    start_date: this.getBulmaCalendar.calendarStartDate(),
                    end_date: this.getBulmaCalendar.calendarEndDate(),
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
          this.chartConfig.colors.blue = this.colorBlue;
          this.chartConfig.colors.green = this.colorGreen;
          this.chartConfig.colors.purple = this.colorPurple;
          this.chartConfig.colors.red = this.colorRed;
          this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentMonthStartDate, this.currentMonthEndDate);
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../../sass/stats-chart';
</style>