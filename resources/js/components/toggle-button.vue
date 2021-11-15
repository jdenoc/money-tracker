<template>
  <Toggle
      v-bind:id="buttonName"
      v-model="toggleButtonstate"
      v-bind="toggleButtonProperties"
  ></Toggle>
</template>

<script>
import Toggle from '@vueform/toggle';

const EMIT_TOGGLE_STATE = 'update:toggleState';

export default {
  name: "ToggleButton",
  components: {Toggle},
  props: {
    buttonName: {type:String, required: true},
    toggleState: {type: Boolean, default: true},
    isDisabled: {type: Boolean, default: false},
    labelChecked: {type: String, default: 'Enabled'},
    labelUnchecked: {type: String, default: 'Disabled'}
  },
  emits: {
    [EMIT_TOGGLE_STATE]: function(payload){
      return typeof payload === 'boolean';
    }
  },
  computed: {
    toggleButtonProperties: function(){
      return {
        disabled: this.isDisabled,
        onLabel: this.labelChecked,
        offLabel: this.labelUnchecked
      }
    },
    toggleButtonstate: {
      get: function(){
        return this.toggleState;
      },
      set: function(updateToggleState){
        this.$emit(EMIT_TOGGLE_STATE, updateToggleState);
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import '~bulma/sass/helpers/color';

.toggle-container{
  --toggle-height: 2rem;
  --toggle-width: 12rem;
  --toggle-font-size: 1.25rem;

  // enbled
  --toggle-bg-on: #{$info};
  --toggle-border-on: #{$info};
  --toggle-text-on: #{$white};
  // disabled
  --toggle-bg-off: #{$grey-light};
  --toggle-border-off: #{$grey-light};
  --toggle-text-off: #{$white};

  :deep(.toggle-handle){  // pass style to child component
    height: var(--toggle-height);
    width: var(--toggle-height);
  }

  :deep(.toggle-label){   // pass style to child component
    font-weight: 500;
  }
}
</style>