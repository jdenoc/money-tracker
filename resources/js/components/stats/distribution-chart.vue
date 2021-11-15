<template>
    <div id="stats-distribution">
        <section id="stats-form-distribution" class="section">
            <account-account-type-toggling-selector
                id="distribution-chart"
                v-model:account-or-account-type-id="accountOrAccountTypeId"
                v-model:account-or-account-type-toggled="accountOrAccountTypeToggle"
            ></account-account-type-toggling-selector>

            <div class="field"><div class="control">
              <ToggleButton
                  button-name="distribution-expense-or-income"
                  v-model:toggle-state="expenseOrIncomeToggle"
                  label-checked="Expense"
                  label-unchecked="Income"
              ></ToggleButton>
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
                v-model:include-transfers="includeTransfers"
            ></include-transfers-checkbox>
            <pie-chart
                v-if="dataLoaded"
                v-bind:chart-data="chartData"
                v-bind:chart-options="chartOptions"
            >Your browser does not support the canvas element.</pie-chart>
        </section>
        <section v-else class="section has-text-centered has-text-weight-semibold is-size-6 stats-results-distribution">
            No data available
        </section>
    </div>
</template>

<script>
    // components
    import AccountAccountTypeTogglingSelector from "../account-account-type-toggling-selector";
    import bulmaCalendar from "../bulma-calendar";
    import IncludeTransfersCheckbox from "../include-transfers-checkbox";
    import PieChart from './chart-defaults/pie-chart';
    import ToggleButton from '../toggle-button';
    // mixins
    import {bulmaColorsMixin} from "../../mixins/bulma-colors-mixin";
    import {entriesObjectMixin} from "../../mixins/entries-object-mixin";
    import {statsChartFormMixin} from "../../mixins/stats-chart-form-mixin";
    import {tagsObjectMixin} from "../../mixins/tags-object-mixin";

    export default {
        name: "distribution-chart",
        mixins: [bulmaColorsMixin, entriesObjectMixin, statsChartFormMixin, tagsObjectMixin],
        components: {IncludeTransfersCheckbox, AccountAccountTypeTogglingSelector, bulmaCalendar, PieChart, ToggleButton},
        data: function(){
            return {
                expenseOrIncomeToggle: true,
                accountOrAccountTypeToggle: true,
                accountOrAccountTypeId: null,
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
                plugins: {
                  title: {
                    display: true,
                    text: this.chartConfig.titleText
                  },
                }
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
        },
        methods: {
            setChartTitle: function(isExpense, startDate, endDate){
                this.chartConfig.titleText = (isExpense ? "Expense" : "Income")
                    +" Distribution ["+startDate+" - "+endDate+"]";
            },

            makeRequest: function(){
                this.$eventBus.broadcast(this.$eventBus.EVENT_LOADING_SHOW());

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
@import '~bulma/sass/helpers/color';
$toggle-button-bg-color: $grey-light;
$toggle-button-text-color: $white;

@import '../../../sass/stats-chart';

:deep(#distribution-expense-or-income+.toggle){  // pass style to child component
  --toggle-height: 2.5rem;
  --toggle-width: 30rem;
  --toggle-font-size: 1rem;

  // Expense
  --toggle-bg-on: #{$toggle-button-bg-color};
  --toggle-border-on: #{$toggle-button-bg-color};
  --toggle-text-on: #{$toggle-button-text-color};
  // Income
  --toggle-bg-off: #{$toggle-button-bg-color};
  --toggle-border-off: #{$toggle-button-bg-color};
  --toggle-text-off: #{$toggle-button-text-color};
}
</style>