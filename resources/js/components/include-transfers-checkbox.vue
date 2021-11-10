<template>
  <div class="field is-pulled-right"><div class="control">
    <input class="is-checkradio is-info is-small is-block has-no-border" type="checkbox"
       v-bind:id="checkboxRadioId"
       v-model="propCheck"
    />
    <label v-bind:for="checkboxRadioId">Include Transfers</label>
  </div></div>
</template>

<script>
export default {
  name: "include-transfers-checkbox",
  props:{
    includeTransfers: {type: Boolean, required: true},
    chartName: {type: String, required: true},
  },
  data: function(){
    return {
      propCheck: this.includeTransfers
    }
  },
  watch:{
    includeTransfers: function(newValue, oldValue){
      this.propCheck = newValue;
    },
    propCheck: function(newValue, oldValue){
      this.$emit('update-checkradio', newValue)
    }
  },
  computed:{
    checkboxRadioId: function(){
      return this.chartName+"-chart-include-transfers";
    }
  },

}
</script>

<style scoped>
  .is-checkradio.is-info.is-small.is-block:checked+label::after{
    top: 0.4rem;
  }
</style>