import {SnotifyStyle} from "vue-snotify";
import _ from "lodash";

export const statsChartMixin = {
  data: function(){
    return {
      dataLoaded: false,
      includeTransfers: false,

      chartConfig: {
        titleText: "Generated data"
      },
    }
  },

  methods: {
    tbdFeatureNotification: function(){
      console.debug("Feature not yet enabled");
      this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, {type: SnotifyStyle.info, message: "Feature not yet enabled"});
    },
    filterIncludeTransferEntries: function(entry){
      // TODO: take into account external transfers (e.g.: transfer_entry_id=0)
      return this.includeTransfers
        || (!this.includeTransfers && Object.prototype.hasOwnProperty.call(entry, 'is_transfer') && !entry.is_transfer);
    },
    getRandomColors: function(colorCount){
      // use of this method requires tailwind-colors.mixin.js
      let excludeColors = ['transparent', 'white', 'black', 'inherit', 'current'];
      let colors = [];
      let selectedColorNames = [];
      let colorNames = this.tailwindColorNames;
      if((colorNames.length-excludeColors.length) < colorCount){
        console.error('randomColor count exceeds available colors');
        return [];
      }
      do{
        let colorNames = this.tailwindColorNames;
        let colorNameIndex = _.random(0, colorNames.length-1, false);
        let colorName = colorNames[colorNameIndex];

        if(!selectedColorNames.includes(colorName) && !excludeColors.includes(colorName)){
          selectedColorNames.push(colorName);
          colors.push( this.randomColor(colorName) );
        }
      }while(colors.length < colorCount);
      return colors;
    }
  },
};