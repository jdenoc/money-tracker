import _ from "lodash";
import {bulmaColorsMixin} from "./bulma-colors-mixin";

export const settingsMixin = {
    mixins: [bulmaColorsMixin],
    data: function(){
        return {
            form: {}    // this is set when component is mounted
        };
    },

    computed: {
        isDataInForm: function(){
            return !_.isNull(this.form.id) && _.isNumber(this.form.id);
        },
        toggleButtonProperties: function(){
            return {
                labels: {unchecked: 'Active', checked: 'Inactive'},
                colors: {unchecked: this.colorInfo, checked: this.colorGreyLight},
                height: 40,
                width: 200,
            };
        },
    },

    methods: {

    }
}

