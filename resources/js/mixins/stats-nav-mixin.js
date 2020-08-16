export const statsNavMixin = {
    data: function(){
        return {
            isVisibleChart: {
                summary: true,
                trending: false,
                tags: false,
                distribution: false
            }
        }
    },

    computed: {
        chartNameSummary: function(){ return 'summary' },
        chartNameTrending: function(){ return 'trending' },
        chartNameTags: function(){ return 'tags' },
        chartNameDistribution: function(){ return 'distribution' }
    },

    methods:{
        makeChartVisible: function(chartToMakeVisible){
            Object.keys(this.isVisibleChart).forEach(function(chartName){
                this.isVisibleChart[chartName] = false;
            }.bind(this));
            if(this.isVisibleChart.hasOwnProperty(chartToMakeVisible)){
                this.isVisibleChart[chartToMakeVisible] = true;
            } else {
                // this.tbdFeatureNotification();
                console.log("FEATURE IS NOT AVAILABLE");
            }
        },
    },
};