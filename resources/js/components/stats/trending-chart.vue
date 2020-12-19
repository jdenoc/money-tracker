<template>
    <div id="stats-trending">
        <section id="stats-form-trending" class="section">
            <account-account-type-toggling-selector
                id="trending-chart"
                v-bind:account-or-account-type-id="accountOrAccountTypeId"
                v-bind:account-or-account-type-toggled="accountOrAccountTypeToggle"
                v-on:update-select="accountOrAccountTypeId = $event"
                v-on:update-toggle="accountOrAccountTypeToggle = $event"
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
                v-bind:include-transfers="includeTransfers"
                v-on:update-checkradio="includeTransfers = $event"
            ></include-transfers-checkbox>
            <line-chart
                v-if="dataLoaded"
                v-bind:chart-data="chartData"
                v-bind:options="chartOptions"
            >Your browser does not support the canvas element.</line-chart>
        </section>
        <section v-else class="section has-text-centered has-text-weight-semibold is-size-6 stats-results-trending">
            No data available
        </section>
    </div>
</template>

<script>
    import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
    import BulmaCalendar from "../bulma-calendar";
    import IncludeTransfersCheckbox from "../include-transfers-checkbox";
    import LineChart from "./chart-defaults/line-chart";
    import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
    import {statsChartMixin} from "../../mixins/stats-chart-mixin";

    export default {
        name: "trending-chart",
        mixins: [entriesObjectMixin, statsChartMixin],
        components: {IncludeTransfersCheckbox, BulmaCalendar, LineChart, AccountAccountTypeTogglingSelector},
        data: function(){
            return {
                chartConfig: {
                    colors: {
                        blue: 'rgba(0, 178, 255, 1)',
                        red: 'rgba(255, 64, 53, 1)',
                        purple: 'rgba(128,0,128,1)',
                    },
                    datasetDefault: {
                        fill: false
                    },
                    timeUnit: 'day'
                },

                accountOrAccountTypeToggle: true,
                accountOrAccountTypeId: '',
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
                )

                return {
                    datasets: [
                        incomeDataset,
                        expenseDataset,
                        comparisonDataset,
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

                this.setChartTitle(chartDataFilterParameters.start_date, chartDataFilterParameters.end_date);
                this.setChartTimeUnit(chartDataFilterParameters.start_date, chartDataFilterParameters.end_date);
                this.multiPageDataFetch(chartDataFilterParameters);
            },
        },
        mounted: function(){
            this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentMonthStartDate, this.currentMonthEndDate);
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../../sass/stats-chart';
</style>