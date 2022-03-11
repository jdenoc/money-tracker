<template>
  <VoerroTagsInput
    id-field="id"
    text-field="name"
    v-model="selectedTagsFromProps"
    v-bind="inputProperties"
  ></VoerroTagsInput>
</template>

<script>
import VoerroTagsInput from '@voerro/vue-tagsinput';

const EMIT_UPDATE_TAGS_INPUT = 'update:selectedTags';

export default {
  name: "tags-input",
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
      selectedTagsFromProps: this.selectedTags
    }
  },

  computed:{
    inputProperties: function(){
      return {
        existingTags: this.existingTags,
        inputId: this.tagsInputName,
        onlyExistingTags: true,
        typeahead: true,
        typeaheadMaxResults: 5,
        typeaheadHideDiscard: true,
      }
    }
  },

  watch:{
    selectedTags: function(newValue, oldValue){
      this.selectedTagsFromProps = newValue;
    },
    selectedTagsFromProps: function(newValue, oldValue){
      this.$emit(EMIT_UPDATE_TAGS_INPUT, newValue);
    }
  },
}
</script>

<style scoped>
@import '~@voerro/vue-tagsinput/dist/style.css';
@import "../../sass/tags-input.scss";
</style>