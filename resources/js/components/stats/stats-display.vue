<template>
    <div id="stats-display" class="ml-2 w-4/5 px-12 pt-6 scroll-mt-16">
        <summary-chart v-show="isVisibleChart.summary"></summary-chart>
        <trending-chart v-show="isVisibleChart.trending"></trending-chart>
        <distribution-chart v-show="isVisibleChart.distribution"></distribution-chart>
        <tags-chart v-show="isVisibleChart.tags"></tags-chart>
    </div>
</template>

<script>
// components
import DistributionChart from "./distribution-chart.vue";
import SummaryChart from "./summary-chart.vue";
import TrendingChart from "./trending-chart.vue";
import TagsChart from "./tags-chart.vue";
// mixins
import {statsNavMixin} from "../../mixins/stats-nav-mixin";

export default {
  name: "stats-display",
  mixins: [statsNavMixin],
  components: {DistributionChart, SummaryChart, TagsChart, TrendingChart},
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_STATS_SUMMARY, function(){
      this.makeChartVisible(this.chartNameSummary);
    }.bind(this));
    this.$eventHub.listen(this.$eventHub.EVENT_STATS_TRENDING, function(){
      this.makeChartVisible(this.chartNameTrending);
    }.bind(this));
    this.$eventHub.listen(this.$eventHub.EVENT_STATS_TAGS, function(){
      this.makeChartVisible(this.chartNameTags);
    }.bind(this));
    this.$eventHub.listen(this.$eventHub.EVENT_STATS_DISTRIBUTION, function(){
      this.makeChartVisible(this.chartNameDistribution);
    }.bind(this));
  }
}
</script>

<style scoped>
</style>