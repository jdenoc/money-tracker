import _ from 'lodash';
import {Institutions} from "../institutions";

export const institutionsObjectMixin = {

  computed: {
    institutionsObject: function(){
      return new Institutions();
    },
    rawInstitutionsData: function(){
      return this.institutionsObject.retrieve;
    },
    areInstitutionsAvailable: function(){
      return !_.isEmpty(this.rawInstitutionsData);
    },
    listInstitutions: function(){
      return _.orderBy(this.rawInstitutionsData, ['name', 'active'], ['asc', 'desc']);
    },
  }

};