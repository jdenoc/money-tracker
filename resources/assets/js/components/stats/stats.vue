<template>
    <div id="stats-display">
        <summary-chart v-show="isVisibleChart.summary"></summary-chart>
        <trending-chart v-show="isVisibleChart.trending"></trending-chart>
        <tags-chart v-show="isVisibleChart.tags"></tags-chart>
    </div>
</template>

<script>
    import SummaryChart from "./summary-chart";
    import TrendingChart from "./trending-chart";
    import TagsChart from "./tags-chart";
    import {statsNavMixin} from "../../mixins/stats-nav-mixin";

    export default {
        name: "stats",
        mixins: [statsNavMixin],
        components: {SummaryChart, TagsChart, TrendingChart},
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
        }
    }
</script>

<style scoped>
    #stats-display{
        margin-left: 0.5rem;
    }
</style>