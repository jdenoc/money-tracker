import _ from 'lodash';

export const decimaliseInputMixin = {

  methods: {
    decimaliseValue: function(inputValue){
      if(!_.isEmpty(inputValue)){
        let sanitisedValue = inputValue.replace(/[^\d.-]/g, '');
        return parseFloat(sanitisedValue).toFixed(2);
      } else {
        return '';
      }
    }
  }
};