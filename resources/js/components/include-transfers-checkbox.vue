<template>
  <div class="field is-pulled-right"><div class="control">
    <input class="is-checkradio is-info is-small is-block has-no-border" type="checkbox"
       v-bind:id="checkboxRadioId"
       v-model="includeTransfersCheckbox"
    />
    <label v-bind:for="checkboxRadioId">Include Transfers</label>
  </div></div>
</template>

<script>
const EMIT_INCLUDE_TRANSFER = 'update:includeTransfers'

export default {
  name: "include-transfers-checkbox",
  props:{
    chartName: {type: String, required: true},
    includeTransfers: {type: Boolean, default: true},
  },
  emits: {
    [EMIT_INCLUDE_TRANSFER]: function(payload){
      return typeof payload === 'boolean';
    }
  },
  computed:{
    checkboxRadioId: function(){
      return this.chartName+"-chart-include-transfers";
    },
    includeTransfersCheckbox: {
      get: function(){
        return this.includeTransfers;
      },
      set: function(value){
        this.$emit(EMIT_INCLUDE_TRANSFER, value);
      }
    }
  },
}
</script>

<style scoped>
  .is-checkradio.is-info.is-small.is-block:checked+label::after{
    top: 0.4rem;
  }
</style>