<template>
    <div id="stats-display">
        <summary-chart v-show="isVisibleChart.summary"></summary-chart>
        <trending-chart v-show="isVisibleChart.trending"></trending-chart>
        <distribution-chart v-show="isVisibleChart.distribution"></distribution-chart>
        <tags-chart v-show="isVisibleChart.tags"></tags-chart>
    </div>
</template>

<script>
    // components
    import DistributionChart from "./distribution-chart";
    import SummaryChart from "./summary-chart";
    import TrendingChart from "./trending-chart";
    import TagsChart from "./tags-chart";
    // mixins
    import {statsNavMixin} from "../../mixins/stats-nav-mixin";

    export default {
        name: "stats",
        mixins: [statsNavMixin],
        components: {DistributionChart, SummaryChart, TagsChart, TrendingChart},
        created: function(){
            this.$eventBus.listen(this.$eventBus.EVENT_STATS_SUMMARY(), function(){
                this.makeChartVisible(this.chartNameSummary);
            }.bind(this));
            this.$eventBus.listen(this.$eventBus.EVENT_STATS_TRENDING(), function(){
                this.makeChartVisible(this.chartNameTrending);
            }.bind(this));
            this.$eventBus.listen(this.$eventBus.EVENT_STATS_TAGS(), function(){
                this.makeChartVisible(this.chartNameTags);
            }.bind(this));
            this.$eventBus.listen(this.$eventBus.EVENT_STATS_DISTRIBUTION(), function(){
                this.makeChartVisible(this.chartNameDistribution);
            }.bind(this));
        }
    }
</script>

<style scoped>
    #stats-display{
        margin-left: 0.5rem;
    }
</style>