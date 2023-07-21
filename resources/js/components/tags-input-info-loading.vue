<template>
  <span class="absolute inset-y-2 right-0 z-10" v-bind:class="{'tags-info-icon': tagsStore.isSet, 'loading' : !tagsStore.isSet}">
    <svg class="animate-spin mr-3 h-5 w-5 text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" v-show="!tagsStore.isSet">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <svg class="w-5 h-5 mr-3 text-black hover:text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
         v-show="tagsStore.isSet"
         v-tooltip="tooltipContent">
      <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
    </svg>
  </span>
</template>

<script>
import {useTagsStore} from "../stores/tags";

export default {
  name: "tags-input-info-loading",
  computed: {
    tagsStore: function(){
      return useTagsStore();
    },
    tooltipContent: function(){
      let tooltipContent = "";
      this.tagsStore.list
        .forEach(function(tag){
          tooltipContent += "&bull; "+tag.name+"<br/>"
        });

      return {
        content: tooltipContent.trim(),
        html: true,
        placement: 'right',
        classes: 'text-xs font-semibold bg-black py-1.5 px-1 rounded rounded-lg text-white tooltip',
      }
    },
  },
}
</script>

<style scoped lang="scss">
</style>