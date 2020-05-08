<template>
    <div>
        <section id="stats-form-tags"  class="section">
            <account-account-type-toggling-selector
                id="tags-chart"
                v-bind:account-or-account-type-id="accountOrAccountTypeId"
                v-bind:account-or-account-type-toggled="accountOrAccountTypeToggle"
                v-on:update-toggle="accountOrAccountTypeToggle = $event"
                v-on:update-select="accountOrAccountTypeId = $event"
            ></account-account-type-toggling-selector>

            <div class="field is-horizontal">
                <div class="field-label is-normal"><label class="label">Tags:</label></div>
                <div class="field-body"><div class="field"><div class="control" v-bind:class="{'is-loading': !areTagsSet}">
                    <voerro-tags-input
                        element-id="stats-tags-chart-tag-input"
                        v-model="chartTagIds"
                        v-bind:existing-tags="listTagsAsObject"
                        v-bind:only-existing-tags="true"
                        v-bind:typeahead="true"
                        v-bind:typeahead-max-results="5"
                    ></voerro-tags-input>
                </div></div></div>
            </div>

            <div class="field">
                <bulma-calendar
                    ref="tagsStatsChartBulmaCalendar"
                ></bulma-calendar>
            </div>

            <div class="field"><div class="control">
                <button class="button is-primary generate-stats" v-on:click="makeRequest"><i class="fas fa-chart-bar"></i>Generate Chart</button>
            </div></div>
        </section>

        <hr />

        <section v-if="areEntriesAvailable" class="section stats-results-tags">
            <bar-chart
                v-if="dataLoaded"
                v-bind:chart-data="this.chartData"
                v-bind:options="this.chartOptions"
            >Your browser does not support the canvas element.</bar-chart>
        </section>
        <section v-else class="section has-text-centered has-text-weight-semibold is-size-6 stats-results-tags">
            No data available
        </section>
    </div>
</template>

<script>
    import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
    import BarChart from "./chart-defaults/bar-chart";
    import BulmaCalendar from '../bulma-calendar';
    import VoerroTagsInput from '@voerro/vue-tagsinput';
    import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
    import {statsChartMixin} from "../../mixins/stats-chart-mixin";
    import {tagsObjectMixin} from "../../mixins/tags-object-mixin";

    export default {
        name: "tags-chart",
        mixins: [entriesObjectMixin, statsChartMixin, tagsObjectMixin],
        components: {AccountAccountTypeTogglingSelector, BarChart, BulmaCalendar, VoerroTagsInput},
        data: function(){
            return {
                chartConfig: {
                    titleText: "Generated data"
                },

                accountOrAccountTypeToggle: true,
                accountOrAccountTypeId: '',
                chartTagIds: []
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
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                };
            },

            getBulmaCalendar: function(){
                return this.$refs.tagsStatsChartBulmaCalendar;
            }
        },
        methods: {
            setChartTitle: function(startDate, endDate){
                this.chartConfig.titleText = "Tags ["+startDate+" - "+endDate+"]";
            },

            standardiseData: function(){
                let standardisedChartData = {};

                this.rawEntriesData
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

            makeRequest: function(){
                this.dataLoaded = false;
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

                if(!_.isEmpty(this.chartTagIds)){
                    chartDataFilterParameters.tags = this.chartTagIds;
                }

                this.setChartTitle(chartDataFilterParameters.start_date, chartDataFilterParameters.end_date);
                this.fetchData(chartDataFilterParameters);
            },
        },
        mounted: function(){
            this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentMonthStartDate, this.currentMonthEndDate);
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../../sass/stats-chart';
    @import '~@voerro/vue-tagsinput/dist/style.css';
    @import '../../../sass/tags-input';

    .field.is-horizontal:nth-child(2){
        margin-bottom: 0;
    }
</style>