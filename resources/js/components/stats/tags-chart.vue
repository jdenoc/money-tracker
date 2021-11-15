<template>
    <div id="stats-tags">
        <section id="stats-form-tags"  class="section">
            <account-account-type-toggling-selector
                id="tags-chart"
                v-model:account-or-account-type-id="accountOrAccountTypeId"
                v-model:account-or-account-type-toggled="accountOrAccountTypeToggle"
            ></account-account-type-toggling-selector>

            <div class="field is-horizontal">
                <div class="field-label is-normal"><label class="label">Tags:</label></div>
                <div class="field-body"><div class="field"><div class="control" v-bind:class="{'is-loading': !areTagsSet}">
                  <TagsInput
                    tagsInputName="stats-tags-chart-tag-input"
                    v-model:tags-input="chartTagIds"
                    v-bind:existing-tags="listTags"
                    v-bind:selected-tags="chartTagIds"
                  ></TagsInput>
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

        <section v-if="areEntriesAvailable && dataLoaded" class="section stats-results-tags">
            <include-transfers-checkbox
                chart-name="tags"
                v-model:include-transfers="includeTransfers"
            ></include-transfers-checkbox>
            <bar-chart
                v-if="dataLoaded"
                v-bind:chart-data="chartData"
                v-bind:chart-options="chartOptions"
            >Your browser does not support the canvas element.</bar-chart>
        </section>
        <section v-else class="section has-text-centered has-text-weight-semibold is-size-6 stats-results-tags">
            No data available
        </section>
    </div>
</template>

<script>
    // utilities
    import _ from 'lodash';
    // components
    import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
    import BarChart from "./chart-defaults/bar-chart";
    import BulmaCalendar from '../bulma-calendar';
    import IncludeTransfersCheckbox from "../include-transfers-checkbox";
    import TagsInput from "../tags-input";
    // mixins
    import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
    import {statsChartFormMixin} from "../../mixins/stats-chart-form-mixin";
    import {tagsObjectMixin} from "../../mixins/tags-object-mixin";

    export default {
        name: "tags-chart",
        mixins: [entriesObjectMixin, statsChartFormMixin, tagsObjectMixin],
        components: {AccountAccountTypeTogglingSelector, BarChart, BulmaCalendar, IncludeTransfersCheckbox, TagsInput},
        data: function(){
            return {
                accountOrAccountTypeToggle: true,
                accountOrAccountTypeId: null,
                chartTagIds: []
            }
        },
        computed: {
            chartData: function(){
              let chartData = this.standardiseData;
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
                plugins: {
                  title: {
                    display: true,
                    text: this.chartConfig.titleText
                  },
                  legend: {
                    display: false
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true
                  }
                }
              }
            },

            getBulmaCalendar: function(){
                return this.$refs.tagsStatsChartBulmaCalendar;
            },

            standardiseData: function(){
                let standardisedChartData = {};

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
                            if(tempDatum.expense){
                                standardisedChartData[key].y -= parseFloat(tempDatum.entry_value);
                            } else {
                                standardisedChartData[key].y += parseFloat(tempDatum.entry_value);
                            }
                            standardisedChartData[key].y = _.round(standardisedChartData[key].y, 2);
                        }.bind(this));
                    }.bind(this), Object.create(null));

                return _.sortBy(
                    Object.values(standardisedChartData),
                    function(o){ return o.x;}
                );
            }
        },
        methods: {
            setChartTitle: function(startDate, endDate){
                this.chartConfig.titleText = "Tags ["+startDate+" - "+endDate+"]";
            },

            makeRequest: function(){
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

                if(!_.isEmpty(this.chartTagIds)){
                    chartDataFilterParameters.tags = this.chartTagIds.map(function(tag){ return tag.id; });
                }

                this.setChartTitle(chartDataFilterParameters.start_date, chartDataFilterParameters.end_date);
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

    .field.is-horizontal:nth-child(2){
        margin-bottom: 0;
    }
</style>