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

        <section v-if="areEntriesAvailable" class="section stats-results-trending">
            <line-chart
                v-if="dataLoaded"
                v-bind:chart-data="this.chartData"
                v-bind:options="this.chartOptions"
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
    import LineChart from "./chart-defaults/line-chart";
    import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
    import {statsChartMixin} from "../../mixins/stats-chart-mixin";

    export default {
        name: "trending-chart",
        mixins: [entriesObjectMixin, statsChartMixin],
        components: {BulmaCalendar, LineChart, AccountAccountTypeTogglingSelector},
        data: function(){
            return {
                chartConfig: {
                    colors: {
                        blue: 'rgba(0, 178, 255, 1)',
                        red: 'rgba(255, 64, 53, 1)',
                    },
                    datasetDefault: {
                        fill: false
                    },
                    timeUnit: 'day',
                    titleText: "Generated data"
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

                return {
                    datasets: [
                        incomeDataset,
                        expenseDataset
                    ],
                    legend: {
                        display: true
                    }
                };
            },
            chartOptions: function(){
                return {
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
                    .filter(function(chartDatum){ return chartDatum.expense === isExpense })
                    .map(function(filteredChartDatum){
                        // extract entry_date and entry_value
                        return {x: filteredChartDatum.entry_date, y: parseFloat(filteredChartDatum.entry_value)}
                    })
                    .sort(function(a, b){
                        // order data by entry_date
                        return (a.x > b.x) ? 1 : (b.x > a.x) ? -1 : 0;
                    })
                    .forEach(function(datum){
                        // condense data points with similar entry_date values
                        let key = datum.x;
                        if(!this[key]){
                            this[key] = {x: datum.x, y: 0};
                            standardisedChartData.push(this[key]);
                        }
                        this[key].y += datum.y;
                        this[key].y = _.round(datum.y, 2);
                    }, Object.create(null));

                return standardisedChartData;
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