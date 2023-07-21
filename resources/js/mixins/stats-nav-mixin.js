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

  methods: {
    makeChartVisible: function(chartToMakeVisible){
      Object.keys(this.isVisibleChart).forEach(function(chartName){
        this.isVisibleChart[chartName] = false;
      }.bind(this));
      if(Object.prototype.hasOwnProperty.call(this.isVisibleChart, chartToMakeVisible)){
        this.isVisibleChart[chartToMakeVisible] = true;
      } else {
        console.warn("FEATURE IS NOT AVAILABLE");
      }
    },
  },
};