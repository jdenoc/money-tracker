import _ from 'lodash';

export const decimaliseInputMixin = {

  methods: {
    decimaliseValue: function(inputValue){
      let sanitisedValue;
      if(!_.isNumber(inputValue)){
        sanitisedValue = inputValue.replace(/[^\d.-]/g, '');
      } else {
        sanitisedValue = inputValue;
      }
      return _.toNumber(sanitisedValue).toFixed(2);
    }
  }
};