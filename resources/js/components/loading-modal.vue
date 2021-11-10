<template>
    <div id="loading-modal" class="modal" v-bind:class="{'is-active' : isLoading}">
        <div class="modal-background"></div>
        <div class="modal-content">
            <div class="container">
              <vue-element-loading v-bind="loaderProperties" />
            </div>
        </div>
    </div>
</template>

<script>
    // utilities
    import randomColor from 'randomcolor/randomColor';
    import _ from 'lodash';
    // components
    import VueElementLoading from "vue-element-loading";

    export default {
      name: "loading-modal",
      components: {
        VueElementLoading
      },
      data: function(){
        return {
          isLoading: false,
          loadingColor: '',
          loadingSpinnerProperties: {},
        };
      },
      computed: {
        loaderProperties: function(){
          let defaultProperties = {
            active: this.isLoading,
            color: this.loadingColor,
            isFullScreen: true,
            backgroundColor: "transparent",
          };
          return _.merge(defaultProperties, this.loadingSpinnerProperties);
        },

        // spinners
        availableSpinners: function(){
          return {
            'spinner': this.spinnerSpinner,
            'ring': this.spinnerRing,
            'line-scale': this.spinnerLineScale,
            'bar-fade-scale': this.spinnerBarFadeScale,
          }
        },
        spinnerSpinner: function(){
          return {
            spinner: 'spinner',
            duration: "0.5",  // seconds is takes to complete one animation
            size: '196',      // px
          }
        },
        spinnerRing: function(){
          return {
            spinner: 'ring',
            duration: '0.6',  // seconds is takes to complete one animation
            size: '196',      // px
          }
        },
        spinnerLineScale: function(){
          return {
            spinner: 'line-scale',
            duration: '0.8',  // seconds is takes to complete one animation
            size: '144',      // px
          }
        },
        spinnerBarFadeScale: function(){
          return {
            spinner: 'bar-fade-scale',
            duration: '1.0',  // seconds is takes to complete one animation
            size: '176',      // px
          }
        },
      },
      methods: {
        showLoading: function(){
          this.randomiseLoader();
          this.isLoading = true;
        },
        stopLoading: function(){
          this.isLoading = false;
        },
        randomiseLoader: function(){
          this.generateRandomColor();
          this.setRandomSpinnerProperties();
        },
        setRandomSpinnerProperties: function(){
          this.loadingSpinnerProperties = _.sample(this.availableSpinners);
        },
        generateRandomColor: function(){
          this.loadingColor = randomColor();
        }
      },
      created: function(){
          this.$eventBus.listen(this.$eventBus.EVENT_LOADING_SHOW(), this.showLoading);
          this.$eventBus.listen(this.$eventBus.EVENT_LOADING_HIDE(), this.stopLoading);
      },
    }
</script>

<style scoped>

</style>