<template>
    <div>
        <div class="buttons">
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.week}"
                v-on:click="displayChartWeek()"
                ><i class="fas fa-calendar-alt"></i>this Week
            </button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.month}"
                v-on:click="displayChartMonth()"
                ><i class="fas fa-calendar-alt"></i>this Month
            </button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.quarter}"
                v-on:click="displayChartQuarter()"
                ><i class="fas fa-calendar-alt"></i>current quarter
            </button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.year}"
                v-on:click="displayChartYear()"
                ><i class="fas fa-calendar-alt"></i>this Year
            </button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isCustomDateRangeVisible}"
                v-on:click="toggleCustomDateRangeVisibility()"
                ><i class="fas"
                    v-bind:class="{'fa-calendar-times': buttons.isCustomDateRangeVisible, 'fa-calendar-plus' : !buttons.isCustomDateRangeVisible}"
                ></i>custom date range
            </button>
        </div>
        <label class="label" v-show="buttons.isCustomDateRangeVisible">
            <bulma-calendar
                ref="trendingBulmaCalendar"
                v-bind:dateRangeUpdateCallback="bulmaDateRangeUpdateCallback"
            ></bulma-calendar>
        </label>

        <line-chart
            v-if="dataLoaded"
            v-bind:chart-data="this.chartData"
            v-bind:options="this.chartOptions"
            >Your browser does not support the canvas element.</line-chart>
    </div>
</template>

<script>
    import BulmaCalendar from "../bulma-calendar";
    import LineChart from "./chart-defaults/line-chart";
    import {statsChartMixin} from "../../mixins/stats-chart-mixin";

    export default {
        name: "trending-chart",
        mixins: [statsChartMixin],
        components: {BulmaCalendar, LineChart},
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
                }
            }
        },
        computed: {
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
                        // yAxes: [{
                        //     display: true,
                        //     // Include a dollar sign in the ticks
                        //     callback: function(value, index, values) {
                        //         return '$' + value;
                        //     }
                        // }]
                    }
                };
            },

            getBulmaCalendar: function(){
                return this.$refs.trendingBulmaCalendar;
            }
        },
        methods: {
            standardiseData: function(isExpense){
                let standardisedChartData = [];
                // this.rawData
                this.rawEntryData
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
                    }, Object.create(null));

                return standardisedChartData;
            },

            fetchData: function(filterParameters){
                this.entriesObject
                    .fetch(0, filterParameters)
                    .then(function(notification){
                        this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
                    }.bind(this))
                    .finally(function(){
                        this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
                        this.dataLoaded = true;
                    }.bind(this));
            },

            displayChartCustomDateRange: function(chartTitlePrefix=''){
                this.dataLoaded = false;
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
                this.chartConfig.titleText = chartTitlePrefix+"Trending ["+this.getBulmaCalendar.calendarStartDate()+" - "+this.getBulmaCalendar.calendarEndDate()+"]";
                this.fetchData({start_date: this.getBulmaCalendar.calendarStartDate(), end_date: this.getBulmaCalendar.calendarEndDate()});
            },

            displayChartYear: function(){
                this.toggleActiveButton('current.year');
                this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentYearStartDate, this.currentYearEndDate);
                this.chartConfig.timeUnit = 'month';
                this.displayChartCustomDateRange("Yearly - ");
            },

            displayChartQuarter: function(){
                this.toggleActiveButton('current.quarter');
                this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentQuarterStartDate, this.currentQuarterEndDate);
                this.chartConfig.timeUnit = 'week';
                this.displayChartCustomDateRange("Quarterly - ");
            },

            displayChartMonth: function(){
                this.toggleActiveButton('current.month');
                this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentMonthStartDate, this.currentMonthEndDate);
                this.chartConfig.timeUnit = 'day';
                this.displayChartCustomDateRange("Monthly - ");
            },

            displayChartWeek: function(){
                this.toggleActiveButton('current.week');
                this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentWeekStartDate, this.currentWeekEndDate);
                this.chartConfig.timeUnit = 'day';
                this.displayChartCustomDateRange("Weekly - ");
            },

            bulmaDateRangeUpdateCallback: function(){
                this.toggleActiveButton();
                this.displayChartCustomDateRange();
            }
        },
    }
</script>

<style scoped>
    .fas{
        padding-right: 0.375rem;
    }
    .buttons{
        margin: 1rem 1rem 0.5rem;
    }
    .label{
        width: 30rem;
        margin: 0 1rem;
    }
</style>