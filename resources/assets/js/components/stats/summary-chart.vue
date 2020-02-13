<template>
    <div>TODO: SUMMARY CHART</div>
</template>

<script>
    export default {
        name: "summary-chart",
        created(){
            this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_HIDE);
        }
    }
</script>

<style scoped>

</style>