<template>
    <div id="stats-tags">
        <section id="stats-form-tags"  class="pb-0 text-sm">
          <account-account-type-toggling-selector
            id="tags-chart"
            class="max-w-lg mt-0 mx-4 mb-4"
            v-bind:account-or-account-type-id.sync="accountOrAccountTypeId"
            v-bind:account-or-account-type-toggled.sync="accountOrAccountTypeToggle"
          ></account-account-type-toggling-selector>

          <div class="max-w-lg mt-0 mx-4 mb-4 grid-cols-4 gap-y-2 gap-x-4">
            <label class="text-sm font-medium justify-self-end py-1 my-0.5">Tags:</label>
            <div class="col-span-3 relative">
              <span class="loading absolute inset-y-2 right-0 z-10" v-show="!tagsStore.isSet">
                <svg class="animate-spin mr-3 h-5 w-5 text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              <tags-input
                  tagsInputName="stats-tags-chart-tag-input"
                  v-bind:existing-tags="tagsStore.list"
                  v-bind:selected-tags.sync="chartTagIds"
              ></tags-input>
            </div>
          </div>

          <date-range class="max-w-lg mt-0 mx-4 mb-4" chart-name="tags-chart" v-bind:start-date.sync="startDate" v-bind:end-date.sync="endDate"></date-range>

          <div class="max-w-lg mt-0 mx-4 mb-4">
            <button class="generate-stats w-full py-2 text-white bg-blue-600 rounded opacity-90 hover:opacity-100" v-on:click="makeRequest">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-1.5 inline-block" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm9 4a1 1 0 10-2 0v6a1 1 0 102 0V7zm-3 2a1 1 0 10-2 0v4a1 1 0 102 0V9zm-3 3a1 1 0 10-2 0v1a1 1 0 102 0v-1z" clip-rule="evenodd" />
              </svg>
              Generate Chart
            </button>
          </div>
        </section>

        <hr class="my-8" />

        <section v-if="entriesStore.isSet && dataLoaded" class="pt-4 stats-results-tags">
            <include-transfers-checkbox
                chart-name="tags"
                v-bind:include-transfers="includeTransfers"
                v-on:update-checkradio="includeTransfers = $event"
            ></include-transfers-checkbox>
            <bar-chart
                v-if="dataLoaded"
                v-bind:chart-data="chartData"
                v-bind:options="chartOptions"
            >Your browser does not support the canvas element.</bar-chart>
        </section>
        <section v-else class="text-center font-semibold text-base stats-results-tags pt-0 overflow-auto">
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
import DateRange from './date-range';
import IncludeTransfersCheckbox from "./include-transfers-checkbox";
import TagsInput from "../tags-input";
// mixins
import {batchEntriesMixin} from "../../mixins/batch-entries-mixin";
import {statsChartMixin} from "../../mixins/stats-chart-mixin";
import {tailwindColorsMixin} from "../../mixins/tailwind-colors-mixin";
// stores
import {useEntriesStore} from "../../stores/entries";
import {useTagsStore} from "../../stores/tags";

export default {
  name: "tags-chart",
  mixins: [batchEntriesMixin, statsChartMixin, tailwindColorsMixin],
  components: {AccountAccountTypeTogglingSelector, BarChart, DateRange, IncludeTransfersCheckbox, TagsInput},
  data: function(){
    return {
      accountOrAccountTypeToggle: true,
      accountOrAccountTypeId: '',
      chartTagIds: [],
      endDate: '',
      startDate: '',
    }
  },
  computed: {
    chartData: function(){
      let chartData = this.standardiseData;
      let chartBgColors = this.getRandomColors(chartData.length);

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
    entriesStore: function(){
      return useEntriesStore();
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
          tempDatum.tags.forEach(function(tagId){
            let key = (tagId === 0) ? 'untagged' : this.tagsStore.find(tagId).name;
            if(!Object.prototype.hasOwnProperty.call(standardisedChartData, key)){
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
    },
    tagsStore: function(){
      return useTagsStore();
    }
  },
  methods: {
    setChartTitle: function(startDate, endDate){
      this.chartConfig.titleText = "Tags ["+startDate+" - "+endDate+"]";
    },

    makeRequest: function(){
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

      if(!_.isEmpty(this.chartTagIds)){
        chartDataFilterParameters.tags = this.chartTagIds.map(function(tag){ return tag.id; });
      }

      this.setChartTitle(chartDataFilterParameters.start_date, chartDataFilterParameters.end_date);
      this.multiPageDataFetch(chartDataFilterParameters);
    },
  }
}
</script>

<style lang="scss" scoped>
// #account-or-account-type-toggling-selector-for-tags-chart obtained
// from the account-account-type-toggling-selector component
::v-deep #account-or-account-type-toggling-selector-for-tags-chart select {
  // tailwind class .w-full
  width: 100%;
}
</style>