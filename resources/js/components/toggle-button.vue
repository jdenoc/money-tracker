<template>
  <VueToggles
      v-bind="toggleButtonProperties"
      v-on:click="toggleStateFromProps = !toggleStateFromProps"
  />
</template>

<script lang="js">
import VueToggles from 'vue-toggles';

const EMIT_UPDATE_TOGGLE = 'update:toggleState';

export default {
  name: "ToggleButton",
  components: {
    VueToggles
  },
  props: {
    colorChecked: {type: String, required: false},        // hex color
    colorUnchecked: {type: String, required: false},      // hex color
    disabled: {type: Boolean, default: false},
    dotColor: {type: String, required: false},            // hex color
    fontSize: {type: Number, required: false},            // px
    labelChecked: {type: String, default: "Enabled"},
    labelUnchecked: {type: String, default: "Disabled"},
    height: {type: Number, required: false},              // px
    reverse: {type: Boolean, default: false},
    textColorChecked: {type: String, required: false},    // hex color
    textColorUnchecked: {type: String, required: false},  // hex color
    toggleId: {type:String, required: true},
    toggleState: {type: Boolean, required: true, default: false},
    width: {type: Number, required: false},               // px
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
        checkedText: this.labelChecked,
        disabled: this.disabled,
        reverse: this.reverse,
        sync: true,
        uncheckedText: this.labelUnchecked,
        value: this.toggleState,
      }

      if(this.colorChecked)
        properties.checkedBg = this.colorChecked;
      if(this.colorUnchecked)
        properties.uncheckedBg = this.colorUnchecked;
      if(this.dotColor)
        properties.dotColor = this.dotColor;
      if(this.fontSize)
        properties.fontSize = this.fontSize;
      if(this.height)
        properties.height = this.height;
      if(this.textColorChecked)
        properties.checkedColor = this.textColorChecked;
      if(this.textColorUnchecked)
        properties.uncheckedColor = this.textColorUnchecked;
      if(this.width)
        properties.width = this.width;

      return properties;
    },
  },
  watch:{
    toggleState: function(newValue){
      // changes passed to the prop.toggleState after initial setup
      this.toggleStateFromProps = newValue;
    },
    toggleStateFromProps: function(newValue){
      // changes made to the data.toggleStateFromProps
      this.$emit(EMIT_UPDATE_TOGGLE, newValue);
    }
  },
}
</script>

<style lang="scss" scoped>
</style>