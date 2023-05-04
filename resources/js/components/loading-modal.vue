<template>
  <div id="loading-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-10" v-show="isLoading">
    <div class="modal flex justify-center relative inset-y-1/4 mx-auto w-160">
      <spinner v-bind="loaderProperties"></spinner>
    </div>
  </div>
</template>

<script>
import Spinner from 'vue-spinner-component/src/Spinner.vue';
import {tailwindColorsMixin} from "../mixins/tailwind-colors-mixin";
import _ from "lodash";

export default {
  name: "loading-modal",
  components: {
    Spinner
  },
  mixins: [tailwindColorsMixin],
  data: function(){
    return {
      isLoading: false,
      loadingColor: '',
    };
  },
  computed: {
    invalidColorNames: function(){
      return [
        'inherit',
        'current',
        'transparent',
        'gray',
        'white',
      ];
    },
    loaderProperties: function(){
      return {
        depth: 5,
        size: 175,
        speed: 0.5,
        color: this.loadingColor
      }
    },
  },
  methods: {
    getRandomValidColorName: function(){
      let colorName = null
      do {
        colorName = this.randomColorName();
      } while (_.includes(this.invalidColorNames, colorName))
      return colorName;
    },
    setRandomColor: function(){
      this.loadingColor = this.randomColor(this.getRandomValidColorName());
    },
    showLoading: function(){
      this.setRandomColor();
      this.isLoading = true;
    },
    stopLoading: function(){
      this.isLoading = false;
    },
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_LOADING_SHOW, this.showLoading);
    this.$eventHub.listen(this.$eventHub.EVENT_LOADING_HIDE, this.stopLoading);
  },
}
</script>

<style scoped>
</style>