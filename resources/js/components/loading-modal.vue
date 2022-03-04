<template>
  <div id="loading-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" v-show="isLoading">
    <div class="modal flex justify-center relative inset-y-1/4 mx-auto w-160">
      <spinner v-bind="loaderProperties"></spinner>
    </div>
  </div>
</template>

<script>
import randomColor from 'randomcolor/randomColor';
import Spinner from 'vue-spinner-component/src/Spinner.vue';

export default {
  name: "loading-modal",
  components: {
    Spinner
  },
  data: function(){
    return {
      isLoading: false,
      loadingColor: '',
    };
  },
  computed: {
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
    showLoading: function(){
      this.generateRandomColor();
      this.isLoading = true;
    },
    stopLoading: function(){
      this.isLoading = false;
    },
    generateRandomColor: function(){
      this.loadingColor = randomColor();
    }
  },
  created: function(){
    this.$eventHub.listen(this.$eventHub.EVENT_LOADING_SHOW, this.showLoading);
    this.$eventHub.listen(this.$eventHub.EVENT_LOADING_HIDE, this.stopLoading);
  },
}
</script>

<style scoped>
</style>