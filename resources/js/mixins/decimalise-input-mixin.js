import _ from 'lodash';

export const decimaliseInputMixin = {

  methods: {
    decimaliseValue: function(inputValue){
      return _.toNumber(inputValue).toFixed(2);
    }
  }
};