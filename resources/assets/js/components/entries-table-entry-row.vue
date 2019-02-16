<template>
    <tr
        v-bind:class="{
            'has-background-warning': !confirm,
            'has-background-success': confirm && !expense,
            'has-text-grey': isFutureEntry,
            'is-confirmed': confirm,
            'is-expense': expense,
            'is-income': !expense,
            'has-attachments': hasAttachments,
            'is-transfer': isTransfer,
            'has-tags': tagIds.length > 0
            }">
        <td><button class="button is-inverted is-info fas fa-edit edit-entry-button" v-on:click="openEditEntryModal(id)"></button></td>
        <td v-text="date" class="row-entry-date"></td>
        <td v-text="memo" class="row-entry-memo"></td>
        <td class="has-text-right" v-bind:class="{'row-entry-value': !expense}">
            <span v-if="expense"></span>
            <span v-else>{{value}}</span>
        </td>
        <td class="has-text-right" v-bind:class="{'row-entry-value': expense}">
            <span v-if="expense">{{value}}</span>
            <span v-else></span>
        </td>
        <td class="row-entry-account-type" v-text="accountTypeName"></td>
        <td class="row-entry-attachment-checkbox"><i v-bind:class="{ 'far fa-square': !hasAttachments, 'fas fa-check-square': hasAttachments }"></i></td>
        <td class="row-entry-transfer-checkbox"><i v-bind:class="{ 'far fa-square': !isTransfer, 'fas fa-check-square': isTransfer }"></i></td>
        <td class="row-entry-tags"><div class="tags">
            <span class="tag is-rounded is-dark" v-for="tagId in tagIds" v-text="getTagName(tagId)"></span>
        </div></td>
    </tr>
</template>

<script>
    import {AccountTypes} from "../account-types";
    import {Tags} from "../tags";

    export default {
        name: "entries-table-entry-row",
        props: ['id', 'date', 'accountTypeId', 'value', 'memo', 'expense', 'confirm', 'disabled', 'hasAttachments', 'isTransfer', 'tagIds'],
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
                this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_MODAL_OPEN, entryId);
            },
            modalNotAvailable: function(entryId){
                alert("Modal not currently available\nEntry "+entryId+" can't be modified at this time");
            }
        }
    }
</script>

<style lang="scss" scoped>
    .tags{
        max-width: 250px;
    }
    .edit-entry-button{
        padding: 5px 10px 5px 14px;
    }

    $warning-bg: rgb(255, 221, 87);
    $success-bg: rgb(35, 209, 96);
    $hover-opacity: 0.25;
    $stripe-odd-opacity: 0.1;
    $stripe-even-opacity: 0.15;

    @mixin set-background-color($background-color){
        background-color: rgba($background-color, $stripe-odd-opacity);
        &:nth-child(even){
            background-color: rgba($background-color, $stripe-even-opacity) !important;
        }
        &:hover {
            background-color: rgba($background-color, $hover-opacity) !important;
        }
        .edit-entry-button:hover{
            background-color: rgba($background-color, $hover-opacity) !important;
        }
    }

    tr.has-background-warning{
        @include set-background-color($warning-bg);
    }
    tr.has-background-success{
        @include set-background-color($success-bg);
    }
</style>