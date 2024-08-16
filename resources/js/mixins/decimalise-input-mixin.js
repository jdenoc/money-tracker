import _ from 'lodash';

export const decimaliseInputMixin = {

  methods: {
    decimaliseValue: function(inputValue){
      if(_.isNumber(inputValue)){
        return _.toNumber(inputValue).toFixed(2);
      }

      if(_.isEmpty(inputValue)){
        return '';
      }

      let sanitisedValue = inputValue.replace(/[^\d.-]/g, '');
      return _.toNumber(sanitisedValue).toFixed(2);
    }
  }
};