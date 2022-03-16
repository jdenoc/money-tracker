import twc from 'tailwindcss/src/public/colors';
import {theme} from '../../../tailwind.config';
import _ from 'lodash';

export const tailwindColorsMixin = {

    computed: {
        tailwindColors: function(){
            // adding values from tailwind.config.js
            return _.merge(twc, theme.extend.colors)
        },
        tailwindColorNames: function(){
            return Object.keys(this.tailwindColors);
        }
    },
    methods: {
        randomColor: function(colorName){
            if(!colorName){
                let colorNames = this.tailwindColorNames;
                let colorNameIndex = _.random(0, colorNames.length-1, false);
                colorName = colorNames[colorNameIndex];
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
    }

}
