import resolveConfig from 'tailwindcss/resolveConfig'
import tailwindConfig from '../../../tailwind.config.js'
import _ from 'lodash';

export const tailwindColorsMixin = {

  computed: {
    tailwindColors: function(){
      return resolveConfig(tailwindConfig).theme.colors;
    },
    tailwindColorNames: function(){
      return Object.keys(this.tailwindColors);
    }
  },
  methods: {
    randomColor: function(colorName){
      if(!colorName){
        colorName = this.randomColorName();
      }
      let color = this.tailwindColors[colorName];

      if(typeof color == 'string'){
        return color;
      } else {
        let colorStrengths = Object.keys(color);
        let colorStrengthIndex = _.random(0, colorStrengths.length-1, false);
        return color[colorStrengths[colorStrengthIndex]];
      }
    },
    randomColorName: function(){
      let colorNames = this.tailwindColorNames;
      let colorNameIndex = _.random(0, colorNames.length-1, false);
      return colorNames[colorNameIndex];
    }
  }

}
