<template>
  <vue-tags-input
      v-model="tag"
      v-bind="tagsInputOptions"
      v-on:tags-changed="handleTagsChanged"
  />
</template>

<script>
import VueTagsInput from "@sipec/vue3-tags-input";

const EMIT_UPDATE_TAGS_INPUT = 'update:tags-input';

export default {
  name: "TagsInput",
  components: {
    VueTagsInput,
  },
  props: {
    existingTags: {type: Array, required: true},
    selectedTags: {type: Array, default: []},
  },
  data: function(){
    return {
      tag: '',
    }
  },
  emits: [
    EMIT_UPDATE_TAGS_INPUT
  ],
  computed: {
    tagsInputOptions: function(){
      return {
        addOnlyFromAutocomplete: true,
        autocompleteItems: this.filteredAutoCompleteItems,
        tags: this.selectedTagsNormalised,
        placeholder: "Add a tag",
      }
    },
    autoCompleteItemsNormalised: function(){
        return this.existingTags.map(this.normaliseTagObjectForInput);
    },
    selectedTagsNormalised: function(){
      return this.selectedTags.map(function(t){
        let tag = this.normaliseTagObjectForInput(t);
        tag.classes = 'tag is-light is-rounded';
        return tag;
      }.bind(this));
    },
    filteredAutoCompleteItems: function() {
      return this.autoCompleteItemsNormalised.filter(i => {
        return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
      });
    }
  },
  methods: {
    handleTagsChanged: function(newTags){
      let cleanedTags = newTags.map(function(t){
        return {
          // this is the original object structure
          id: t.id,
          name: t.text,
        }
      });
      this.$emit(EMIT_UPDATE_TAGS_INPUT, cleanedTags);
    },
    normaliseTagObjectForInput: function(tag){
      return {
        id: tag.id,
        text: tag.name,
        classes: '',
        style: '',
      }
    }
  }
}
</script>

<style scoped lang="scss">
@import '~bulma/sass/helpers/color';

// we change the border color if the user focuses the input
.vue-tags-input.ti-focus {
  :deep(.ti-input){
    border: 1px solid #{$blue};
  }
}

.vue-tags-input{
  max-width: none;

  // selected tag in input
  :deep(.ti-tag){
    font-weight: 500;
  }
  // highlighted auto-complete value
  :deep(.ti-autocomplete .ti-selected-item){
    background: #{$info};
  }
}
</style>