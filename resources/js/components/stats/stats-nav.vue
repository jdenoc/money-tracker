<template>
    <nav class="panel">
        <p class="panel-heading">Stats</p>
        <a class="panel-block"
            v-on:click="showSummaryChart"
            v-bind:class="{'is-active': isVisibleChart.summary}"
            >
            <span class="panel-icon">
                <i class="fas fa-book" aria-hidden="true"></i>
            </span> Summary
        </a>
        <a class="panel-block"
            v-on:click="showTrendingChart"
            v-bind:class="{'is-active': isVisibleChart.trending}"
            >
            <span class="panel-icon">
                <i class="fas fa-chart-area" aria-hidden="true"></i>
            </span> Trending
        </a>
        <a class="panel-block"
           v-on:click="showDistributionChart"
           v-bind:class="{'is-active': isVisibleChart.distribution}"
        >
            <span class="panel-icon">
                <i class="fas fa-chart-pie" aria-hidden="true"></i>
            </span> Distribution
        </a>
        <a class="panel-block"
            v-on:click="showTagsChart"
            v-bind:class="{'is-active': isVisibleChart.tags}"
            >
            <span class="panel-icon">
                <i class="fas fa-chart-bar" aria-hidden="true"></i>
            </span> Tags
        </a>
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