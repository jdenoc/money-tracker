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
    selectedTags: function(newValue){
      this.selectedTagsFromProps = newValue;
    },
    selectedTagsFromProps: function(newValue){
      this.$emit(EMIT_UPDATE_TAGS_INPUT, newValue);
    }
  },
}
</script>

<style scoped lang="scss">
@import "~@voerro/vue-tagsinput/dist/style.css";

/* additional styling for voerro-tags-input component */
::v-deep .tags-input {
  /* taken from the tailwindcss [type='text'] selector */
  border-color: #6b7280;

  input {
    font-size: 0.8rem;
    padding: 1px 2px;
    line-height: normal;

    &:focus {
      box-shadow: none;
    }
  }

  &.active {
    /* taken from the tailwindcss [type='text']:focus selector */
    outline: 2px solid transparent;
    outline-offset: 2px;
    --tw-ring-offset-width: 0;
    --tw-ring-offset-color: #fff;
    --tw-ring-color: #2563eb;
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
    border-color: #2563eb;
  }
}

::v-deep .typeahead-badges {
  margin-top: 0.25rem;
}
</style>