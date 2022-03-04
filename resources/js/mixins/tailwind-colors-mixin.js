import twc from 'tailwindcss/src/public/colors';
import {theme} from '../../../tailwind.config';
import _ from 'lodash';

export const tailwindColorsMixin = {

    computed: {
        tailwindColors: function(){
            // adding values from tailwind.config.js
            return _.merge(twc, theme.extend.colors)
        },
    }

}
