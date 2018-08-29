<template>
    <div class="box field is-grouped">
        <div class="control is-expanded">
            <div class="attachment-name" v-text="name"></div>
        </div>
        <div class="control">
            <button class="button" v-on:click="viewEntryAttachment"><i class="fas fa-search"></i></button>
            <button class="button is-danger" v-on:click="deleteEntryAttachment"><i class="fas fa-trash-alt"></i></button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "entry-modal-attachment",
        props: [ 'uuid','name'],
        methods: {
            viewEntryAttachment: function(){
                // TODO: open attachment in new tab
                this.notAvailable()
            },
            deleteEntryAttachment: function(){
                // TODO: sent DELETE request to delete attachment
                this.notAvailable();
            },
            notAvailable: function(){
                alert("This feature is not currently available");
            },
        }
    }
</script>

<style scoped>
    .box{
        padding: 0.5rem
    }
    .attachment-name{
        padding: 0.375rem 0 0.375rem 1.25rem;
    }
</style>