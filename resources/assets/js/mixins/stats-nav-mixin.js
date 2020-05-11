export const statsNavMixin = {
    data: function(){
        return {
            isVisibleChart: {
                summary: true,
                trending: false,
                tags: false
            }
        }
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