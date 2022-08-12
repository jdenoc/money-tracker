import _ from "lodash";
import {tailwindColorsMixin} from "./tailwind-colors-mixin";

export const settingsMixin = {
    mixins: [tailwindColorsMixin],
    data: function(){
        return {
            form: {}    // this is set when component is mounted
        };
    },

    computed: {
        isDataInForm: function(){
            return !_.isNull(this.form.id) && _.isNumber(this.form.id);
        },
        defaultToggleButtonProperties: function(){
            return {
                colorChecked: this.tailwindColors.blue[600],
                colorUnchecked: this.tailwindColors.gray[400],
                fontSize: 14, // px
                labelChecked: 'Active',
                labelUnchecked: 'Inactive',
                height: 40, // px
                width: 250, // px
                transitionSpeed: 75, // ms
                reverse: true,
            };
        },
        altDefaultToggleButtonProperties: function(){
            let properties = _.cloneDeep(this.defaultToggleButtonProperties);
            properties.colorChecked = this.tailwindColors.gray[400];
            properties.colorUnchecked = this.tailwindColors.blue[600];
            properties.labelChecked = 'Inactive';
            properties.labelUnchecked = 'Active';
            properties.reverse = false;
            return properties;
        }
    },

    methods: {
        featureUnavailable: function(){
            console.debug("This feature is currently available");
        },
        makeDateReadable(isoDateString){
            return new Date(isoDateString).toString();
        },
        debugOutput(msg){   // TODO: remove
            console.debug(new Date().getTime()+' '+msg);
        }
    }
}

