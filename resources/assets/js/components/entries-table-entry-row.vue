<template>
    <tr
        v-bind:class="{'has-background-warning': confirm && expense, 'has-background-success': confirm && !expense, 'has-text-grey-light' : isFutureEntry}">
        <td><button class="button is-inverted is-info fas fa-edit edit-entry-button" v-on:click="openEditEntryModal(id)"></button></td>
        <td v-text="date"></td>
        <td v-text="memo"></td>
        <td class="has-text-right">
            <span v-if="expense"></span>
            <span v-else>${{value}}</span>
        </td>
        <td class="has-text-right">
            <span v-if="expense">${{value}}</span>
            <span v-else></span>
        </td>
        <td v-text="accountTypeName"></td>
        <td><i v-bind:class="{ 'far fa-square': !hasAttachments, 'fas fa-check-square': hasAttachments }"></i></td>
        <td><div class="tags">
            <span class="tag is-rounded is-dark" v-for="tagId in tagIds" v-text="getTagName(tagId)"></span>
        </div></td>
    </tr>
</template>

<script>
    import {AccountTypes} from "../account-types";
    import {Tags} from "../tags";

    export default {
        name: "entries-table-entry-row",
        props: ['id', 'date', 'accountTypeId', 'value', 'memo', 'expense', 'confirm', 'disabled', 'hasAttachments', 'tagIds'],
        data: function(){
            return {
                millisecondsPerMinute: 60000
            }
        },
        computed: {
            isFutureEntry: function(){
                let timezoneOffset = new Date().getTimezoneOffset()*this.millisecondsPerMinute;
                return Date.parse(this.date)+timezoneOffset > Date.now();
            },
            accountTypeName: function(){
                return new AccountTypes().getNameById(this.accountTypeId);
            },
        },
        methods: {
            getTagName: function(tagId){
                return new Tags().getNameById(tagId);
            },
            openEditEntryModal: function(entryId){
                this.modalNotAvailable(entryId);
            },
            modalNotAvailable: function(entryId){
                alert("Modal not currently available\nEntry "+entryId+" can't be modified at this time");
            }
        }
    }
</script>

<style scoped>
    .tags{
        max-width: 250px;
    }
    .edit-entry-button{
        padding: 5px 10px 5px 14px;
    }
</style>