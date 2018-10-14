<template>
    <div id="loading-modal" class="modal" v-bind:class="{'is-active' : isLoading}">
        <div class="modal-background"></div>
        <div class="modal-content">
            <div class="container">
                <spinner
                    v-bind:depth="spinner.depth"
                    v-bind:size="spinner.size"
                    v-bind:speed="spinner.speed"
                    v-bind:color="spinner.color"
                ></spinner>
            </div>
        </div>
    </div>
</template>

<script>
    import randomColor from 'randomColor';
    import Spinner from 'vue-spinner-component/src/Spinner.vue';

    export default {
        name: "loading-modal",
        components: {
            Spinner
        },
        data: function(){
            return {
                isLoading: false,
                spinner: {
                    depth: 5,
                    size: 175,
                    speed: 0.5,
                    color: ""
                },
            };
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
                this.spinner.color = randomColor();
            }
        },
        created: function(){
            this.$eventHub.listen(this.$eventHub.EVENT_SHOW_LOADING, this.showLoading);
            this.$eventHub.listen(this.$eventHub.EVENT_STOP_LOADING, this.stopLoading);
        },
    }
</script>

<style scoped>
    .container {
        width: 100px;
    }
</style>