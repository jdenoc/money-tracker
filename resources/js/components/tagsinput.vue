<template>
  <VoerroTagsInput
      v-bind:element-id="tagsInputName"
      id-field="id"
      text-field="name"
      v-model="propSelectedTags"
      v-bind:existing-tags="existingTags"
      v-bind:only-existing-tags="true"
      v-bind:typeahead="true"
      v-bind:typeahead-hide-discard="true"
      v-bind:typeahead-max-results="5"
  ></VoerroTagsInput>
</template>

<script>
import VoerroTagsInput from '@voerro/vue-tagsinput';

export default {
  name: "tagsinput",
  components: {
    VoerroTagsInput,
  },
  props: {
    tagsInputName: {type: String, required: true},
    existingTags: {type: Array, required: true},
    selectedTags: {type: Array, required: true, default: function(){ return []} }
  },

  data: function(){
    return {
      propSelectedTags: this.selectedTags
    }
  },

  watch:{
    selectedTags: function(newValue, oldValue){
      this.propSelectedTags = newValue;
    },
    propSelectedTags: function(newValue, oldValue){
      this.$emit('update-tags-input', newValue);
    }
  },
}
</script>

<style scoped>
  @import '~@voerro/vue-tagsinput/dist/style.css';

  .tags-input input{
    font-size: 0.75rem;
  }
  .tags-input span{
    margin: 0 0.3rem 0 0;
  }
  .typeahead-badges{
    margin-top: 0.125rem;
  }
</style>