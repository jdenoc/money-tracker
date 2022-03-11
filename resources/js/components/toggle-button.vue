<template>
  <BaseToggleButton
      v-model="toggleStateFromProps"
      v-bind="toggleButtonProperties"
  />
</template>

<script lang="js">
import { ToggleButton } from 'vue-js-toggle-button';

const EMIT_UPDATE_TOGGLE = 'update:toggleState';

export default {
  name: "ToggleButton",
  components: {
    BaseToggleButton: ToggleButton
  },
  props: {
    colorChecked: {required: false, default: ''},
    colorUnchecked: {required: false, default: ''},
    disabled: {type: Boolean, default: false},
    fontSize: {type: Number},
    labelChecked: {type: String, default: "Enabled"},
    labelUnchecked: {type: String, default: "Disabled"},
    height: {type: Number, default: null},
    toggleId: {type:String, required: true},
    toggleState: {type: Boolean, required: true, default: false},
    width: {type: Number, default: null},
  },
  data: function(){
    return {
      toggleStateFromProps: this.toggleState,
    }
  },
  computed: {
    toggleButtonProperties: function(){
      let properties = {
        id: this.toggleId,
        labels: {checked: this.labelChecked, unchecked: this.labelUnchecked},
        color: {checked: this.colorChecked, unchecked: this.colorUnchecked},
        disabled: this.disabled,
        sync: true,
      }

      if(this.fontSize)
        properties.fontSize = this.fontSize;
      if(this.height)
        properties.height = this.height;
      if(this.width)
        properties.width = this.width;

      return properties;
    },
  },
  watch:{
    toggleState: function(newValue, oldValue){
      // changes passed to the prop.toggleState after initial setup
      this.toggleStateFromProps = newValue;
    },
    toggleStateFromProps: function(newValue, oldValue){
      // changes made to the data.toggleStateFromProps
      this.$emit(EMIT_UPDATE_TOGGLE, newValue);
    }
  },
}
</script>

<style lang="scss" scoped>
</style>