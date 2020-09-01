<template>
    <div id="stats-distribution">
        <section id="stats-form-distribution" class="section">
            <account-account-type-toggling-selector
                id="distribution-chart"
                v-bind:account-or-account-type-id="accountOrAccountTypeId"
                v-bind:account-or-account-type-toggled="accountOrAccountTypeToggle"
                v-on:update-select="accountOrAccountTypeId = $event"
                v-on:update-toggle="accountOrAccountTypeToggle = $event"
            ></account-account-type-toggling-selector>

            <div class="field"><div class="control">
                <toggle-button
                    id="distribution-expense-or-income"
                    v-model="expenseOrIncomeToggle"
                    v-bind:value="expenseOrIncomeToggle"
                    v-bind:labels="toggleButtonProperties.labels"
                    v-bind:color="toggleButtonProperties.colors"
                    v-bind:height="toggleButtonProperties.height"
                    v-bind:width="toggleButtonProperties.width"
                    v-bind:sync="true"
                />
            </div></div>

            <div class="field">
                <bulma-calendar
                    ref="distributionStatsChartBulmaCalendar"
                ></bulma-calendar>
            </div>

            <div class="field"><div class="control">
                <button class="button is-primary generate-stats" v-on:click="makeRequest"><i class="fas fa-chart-pie"></i>Generate Chart</button>
            </div></div>
        </section>
        <hr/>
        <section v-if="areEntriesAvailable && dataLoaded" class="section stats-results-distribution">
            <include-transfers-checkbox
                chart-name="distribution"
                v-bind:include-transfers="includeTransfers"
                v-on:update-checkradio="includeTransfers = $event"
            ></include-transfers-checkbox>
            <pie-chart
                v-if="dataLoaded"
                v-bind:chart-data="this.chartData"
                v-bind:options="this.chartOptions"
            >Your browser does not support the canvas element.</pie-chart>
        </section>
        <section v-else class="section has-text-centered has-text-weight-semibold is-size-6 stats-results-distribution">
            No data available
        </section>
    </div>
</template>

<script>
    import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
    import bulmaCalendar from "../bulma-calendar";
    import IncludeTransfersCheckbox from "../include-transfers-checkbox";
    import PieChart from './chart-defaults/pie-chart';
    import {ToggleButton} from 'vue-js-toggle-button';

    import {bulmaColorsMixin} from "../../mixins/bulma-colors-mixin";
    import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
    import {statsChartMixin} from "../../mixins/stats-chart-mixin";
    import {tagsObjectMixin} from "../../mixins/tags-object-mixin";

    export default {
        name: "distribution-chart",
        mixins: [bulmaColorsMixin, entriesObjectMixin, statsChartMixin, tagsObjectMixin],
        components: {IncludeTransfersCheckbox, AccountAccountTypeTogglingSelector, bulmaCalendar, PieChart, ToggleButton},
        data: function(){
            return {
                expenseOrIncomeToggle: true,
                accountOrAccountTypeToggle: true,
                accountOrAccountTypeId: '',
            }
        },
        computed: {
            getBulmaCalendar: function(){
                return this.$refs.distributionStatsChartBulmaCalendar;
            },
            chartData: function(){
                let chartData = this.standardiseData;
                let chartBgColors = [];
                for(let i=0; i<chartData.length; i++){
                    chartBgColors.push(this.randomColor());
                }

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
                    colors: {checked: this.colorGreyLight, unchecked: this.colorGreyLight},
                    labels: {checked: 'Expense', unchecked: 'Income'},
                    height: 40,
                    width: 475,
                };
            },
        },
        methods: {
            setChartTitle: function(isExpense, startDate, endDate){
                this.chartConfig.titleText = (isExpense ? "Expense" : "Income")
                    +" Distribution ["+startDate+" - "+endDate+"]";
            },

            makeRequest: function(){
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);

                let chartDataFilterParameters = {
                    start_date: this.getBulmaCalendar.calendarStartDate(),
                    end_date: this.getBulmaCalendar.calendarEndDate(),
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
        },
        mounted: function(){
            this.getBulmaCalendar.setBulmaCalendarDateRange(this.currentMonthStartDate, this.currentMonthEndDate);
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../../sass/stats-chart';

    .vue-js-switch#distribution-expense-or-income{
        font-size: 1rem;
    }
</style>