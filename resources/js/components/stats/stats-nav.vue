<template>
  <!-- stats - sidebar -->
  <nav id="stats-nav" class="w-80 top-16 left-0 inset-y-0 fixed border-r">
    <div id="stats-panel-header" class="block py-4 px-3 font-semibold text-xl bg-gray-100">Stats</div>

    <ul>
      <li class="py-2 px-3 cursor-pointer border-b stats-nav-option"
          v-on:click="showSummaryChart"
          v-bind:class="{'bg-blue-600 text-white is-active': isVisibleChart.summary, 'bg-white hover:bg-gray-50 text-black hover:text-blue-600': !isVisibleChart.summary}"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
          <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
          <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
        </svg>
        Summary
      </li>

      <li class="py-2 px-3 cursor-pointer border-b stats-nav-option"
          v-on:click="showTrendingChart"
          v-bind:class="{'bg-blue-600 text-white is-active': isVisibleChart.trending, 'bg-white hover:bg-gray-50 text-black hover:text-blue-600': !isVisibleChart.trending}"
      >
        <span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
          </svg>
          Trending
        </span>
      </li>

      <li class="py-2 px-3 cursor-pointer border-b stats-nav-option"
           v-on:click="showDistributionChart"
           v-bind:class="{'bg-blue-600 text-white is-active': isVisibleChart.distribution, 'bg-white hover:bg-gray-50 text-black hover:text-blue-600': !isVisibleChart.distribution}"
      >
        <span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
          </svg>
          Distribution
        </span>
      </li>

      <li class="py-2 px-3 cursor-pointer border-b stats-nav-option"
          v-on:click="showTagsChart"
          v-bind:class="{'bg-blue-600 text-white is-active': isVisibleChart.tags, 'bg-white hover:bg-gray-50 text-black hover:text-blue-600': !isVisibleChart.tags}"
      >
        <span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block -mt-1 mr-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm9 4a1 1 0 10-2 0v6a1 1 0 102 0V7zm-3 2a1 1 0 10-2 0v4a1 1 0 102 0V9zm-3 3a1 1 0 10-2 0v1a1 1 0 102 0v-1z" clip-rule="evenodd" />
          </svg>
          Tags
        </span>
      </li>
    </ul>
  </nav>
</template>

<script>
import {statsNavMixin} from '../../mixins/stats-nav-mixin';

export default {
  name: "stats-nav",
  mixins: [statsNavMixin],
  methods:{
    showSummaryChart: function(){
      this.makeChartVisible(this.chartNameSummary);
      this.$eventHub.broadcast(this.$eventHub.EVENT_STATS_SUMMARY);
    },
    showTrendingChart: function(){
      this.makeChartVisible(this.chartNameTrending);
      this.$eventHub.broadcast(this.$eventHub.EVENT_STATS_TRENDING);
    },
    showTagsChart: function(){
      this.makeChartVisible(this.chartNameTags);
      this.$eventHub.broadcast(this.$eventHub.EVENT_STATS_TAGS);
    },
    showDistributionChart: function(){
      this.makeChartVisible(this.chartNameDistribution);
      this.$eventHub.broadcast(this.$eventHub.EVENT_STATS_DISTRIBUTION);
    }
  },
  mounted: function(){
    // check which chart is supposed to be visible, then broadcast which chart should be visible
    let visibleChart = Object.keys(this.isVisibleChart).filter(function(chartName){
      return this.isVisibleChart[chartName] === true
    }.bind(this))[0];
    this['show'+visibleChart.charAt(0).toUpperCase()+visibleChart.slice(1)+'Chart']();
  }
}
</script>

<style scoped>
</style>