<template>
    <div>
        <div class="buttons">
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.year}"
                v-on:click="displayChartYear(true)"
            ><i class="fas fa-calendar-alt"></i>current Year</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.previous.year}"
                v-on:click="displayChartYear(false)"
            ><i class="fas fa-calendar-alt"></i>previous Year</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.quarter}"
                v-on:click="displayChartQuarter(true)"
            ><i class="fas fa-calendar-alt"></i>current quarter</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.previous.quarter}"
                v-on:click="displayChartQuarter(false)"
            ><i class="fas fa-calendar-alt"></i>previous quarter</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.month}"
                v-on:click="displayChartMonth(true)"
            ><i class="fas fa-calendar-alt"></i>current Month</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.previous.month}"
                v-on:click="displayChartMonth(false)"
            ><i class="fas fa-calendar-alt"></i>previous Month</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.week}"
                v-on:click="displayChartWeek(true)"
            ><i class="fas fa-calendar-alt"></i>current Week</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.previous.week}"
                v-on:click="displayChartWeek(false)"
            ><i class="fas fa-calendar-alt"></i>previous Week</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.current.day}"
                v-on:click="displayChartDay(true)"
            ><i class="fas fa-calendar-alt"></i>today</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isActive.previous.day}"
                v-on:click="displayChartDay(false)"
            ><i class="fas fa-calendar-alt"></i>yesterday</button>
            <button class="button is-info is-light"
                v-bind:class="{'is-active': buttons.isCustomDateRangeVisible}"
                v-on:click="toggleCustomDateRangeVisibility"
                ><i class="fas"
                    v-bind:class="{'fa-calendar-times': buttons.isCustomDateRangeVisible, 'fa-calendar-plus' : !buttons.isCustomDateRangeVisible}"
            ></i>custom date range</button>
        </div>
        <label class="label" v-show="buttons.isCustomDateRangeVisible">
            <bulma-calendar
                ref="tagsBulmaCalendar"
                v-bind:dateRangeUpdateCallback="bulmaDateRangeUpdateCallback"
            ></bulma-calendar>
        </label>

        <bar-chart
            v-if="dataLoaded"
            v-bind:chart-data="this.chartData"
            v-bind:options="this.chartOptions"
        >Your browser does not support the canvas element.</bar-chart>
    </div>
</template>

<script>
    import BarChart from "./chart-defaults/bar-chart";
    import BulmaCalendar from '../bulma-calendar';
    import {statsChartMixin} from "../../mixins/stats-chart-mixin";

    export default {
        name: "tags-chart",
        mixins: [statsChartMixin],
        components: {BarChart, BulmaCalendar},
        data: function(){
            return {
                chartConfig: {
                    titleText: "Generated data"
                }
            }
        },
        computed: {
            chartData: function(){
                let chartData = this.standardiseData();
                let chartBgColors = [];
                for(let i=0; i<chartData.length; i++){
                    chartBgColors.push(this.randomColor());
                }
                return {
                    labels: chartData.map(function(d){ return d.x }),
                    datasets: [{
                        data: chartData,
                        backgroundColor: chartBgColors
                    }]
                };
            },
            chartOptions: function(){
                return {
                    maintainAspectRatio: false,
                    title: {
                        display: true,
                        text: this.chartConfig.titleText
                    },
                    legend: {
                        display: false
                    },
                    scales: {
                    //     xAxes: [{
                    //         display: true,
                    //         scaleLabel: {
                    //             display: true,
                    //             labelString: 'entry date'
                    //         },
                    //         type: 'time',
                    //         time: {
                    //             unit: this.chartConfig.timeUnit,
                    //         },
                    //         ticks: {
                    //             autoSkip: true,
                    //             maxRotation: 90,
                    //             minRotation: 90
                    //         }
                    //     }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        //     display: true,
                        //     // Include a dollar sign in the ticks
                        //     callback: function(value, index, values) {
                        //         return '$' + value;
                        //     }
                        }]
                    }
                };
            },

            getBulmaCalendar: function(){
                return this.$refs.tagsBulmaCalendar;
            }
        },
        methods: {
            fetchData: function(filterParameters){
                this.tagsObject.fetch();

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

            standardiseData: function(){
                let standardisedChartData = {};

                this.rawEntryData
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
                            if(tempDatum.expense){
                                standardisedChartData[key].y -= parseFloat(tempDatum.entry_value);
                            } else {
                                standardisedChartData[key].y += parseFloat(tempDatum.entry_value);
                            }
                            standardisedChartData[key].y = _.round(standardisedChartData[key].y, 2);
                        }.bind(this));
                    }.bind(this), Object.create(null));

                return Object.values(standardisedChartData);
            },

            bulmaDateRangeUpdateCallback: function(){
                this.toggleActiveButton();
                this.displayChartCustomDateRange();
            },

            displayChartCustomDateRange: function(chartTitlePrefix=''){
                this.dataLoaded = false;
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
                this.chartConfig.titleText = chartTitlePrefix+"Tags ["+this.getBulmaCalendar.calendarStartDate()+" - "+this.getBulmaCalendar.calendarEndDate()+"]";
                this.fetchData({start_date: this.getBulmaCalendar.calendarStartDate(), end_date: this.getBulmaCalendar.calendarEndDate()});
            },

            displayChartYear: function(isCurrentYear){
                if(isCurrentYear){
                    this.toggleActiveButton('current.year');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentYearStartDate, this.currentYearEndDate);
                } else {
                    this.toggleActiveButton('previous.year');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.previousYearStartDate, this.previousYearEndDate);
                }
                this.displayChartCustomDateRange("Yearly - ");
            },

            displayChartQuarter: function(isCurrentQuarter){
                if(isCurrentQuarter){
                    this.toggleActiveButton('current.quarter');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentQuarterStartDate, this.currentQuarterEndDate);
                } else {
                    this.toggleActiveButton('previous.quarter');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.previousQuarterStartDate, this.previousQuarterEndDate);
                }
                this.displayChartCustomDateRange("Quarterly - ");
            },

            displayChartMonth: function(isCurrentMonth){
                if(isCurrentMonth){
                    this.toggleActiveButton('current.month');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentMonthStartDate, this.currentMonthEndDate);
                } else {
                    this.toggleActiveButton('previous.month');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.previousMonthStartDate, this.previousMonthEndDate);
                }
                this.displayChartCustomDateRange("Monthly - ");
            },

            displayChartWeek: function(isCurrentWeek){
                if(isCurrentWeek){
                    this.toggleActiveButton('current.week');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentWeekStartDate, this.currentWeekEndDate);
                } else {
                    this.toggleActiveButton('previous.week');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.previousWeekStartDate, this.previousWeekEndDate);
                }
                this.displayChartCustomDateRange("Weekly - ");
            },

            displayChartDay: function(isCurrentDay){
                if(isCurrentDay){
                    this.toggleActiveButton('current.day');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.today, this.today);
                    this.displayChartCustomDateRange("Today - ");
                } else {
                    this.toggleActiveButton('previous.day');
                    this.getBulmaCalendar.setBulmaCalendarDateRange(this.yesterday, this.yesterday);
                    this.displayChartCustomDateRange("Yesterday - ");
                }

            }
        }
    }
</script>

<style scoped>
    .fas{
        padding-right: 0.375rem;
    }
    .buttons{
        margin: 1rem 1rem 0.5rem;
    }
    .buttons .button{
        width: 11rem;
    }
    .label{
        width: 30rem;
        margin: 0 1rem;
    }
</style>