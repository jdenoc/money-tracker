<template>
  <label v-bind:for="checkboxRadioId" class="rounded-md py-0.5 px-2 inline-flex cursor-pointer text-sm float-right"
         v-bind:class="{
            'bg-gray-50 hover:bg-gray-100 text-black': !propCheck,
            'bg-blue-500 bg-opacity-90 text-white hover:bg-opacity-100': propCheck,
          }"
  >
    <input type="checkbox" class="appearance-none hidden"
           v-bind:id="checkboxRadioId"
           v-model="propCheck"
    />
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
         v-bind:class="{
             'text-white': propCheck,
             'text-gray-100 opacity-90': !propCheck,
         }"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    Include Transfers
  </label>
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
    includeTransfers: function(newValue){
      this.propCheck = newValue;
    },
    propCheck: function(newValue){
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
</style>