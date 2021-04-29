import _ from 'lodash';
import {Institutions} from "../institutions";

export const institutionsObjectMixin = {
    data: function(){
        return {
            institutionsObject: new Institutions()
        }
    },

    computed: {
        rawInstitutionsData: function(){
            return this.institutionsObject.retrieve;
        },
        areInstitutionsAvailable: function(){
            return !_.isEmpty(this.rawInstitutionsData);
        },
    },

    methods: {
        fetchInstitutions: function(){
            return this.institutionsObject.fetch();
        }
    }
};