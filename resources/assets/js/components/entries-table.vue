<template>
    <table id="entry-table" class="table is-narrow is-striped is-hoverable is-fullwidth">
        <thead>
            <tr>
                <th></th>
                <th>Date</th>
                <th>Memo</th>
                <th class="has-text-right">Income</th>
                <th class="has-text-right">Expense</th>
                <th>Type</th>
                <th><i class="fas fa-paperclip"></i></th>
                <th><i class="fas fa-tags"></i></th>
            </tr>
        </thead>
        <tbody>
            <entries-table-entry-row
                v-for="entry in listOfEntries"
                v-bind:key="entry.id"
                v-bind:id="entry.id"
                v-bind:date="entry.entry_date"
                v-bind:accountTypeId="entry.account_type_id"
                v-bind:value="entry.entry_value"
                v-bind:memo="entry.memo"
                v-bind:expense="entry.expense"
                v-bind:confirm="entry.confirm"
                v-bind:disabled="entry.disabled"
                v-bind:hasAttachments="entry.has_attachments"
                v-bind:tagIds="entry.tags"
            ></entries-table-entry-row>
        </tbody>
    </table>
</template>

<script>
    import {Entries} from '../entries';
    import EntriesTableEntryRow from "./entries-table-entry-row";

    export default {
        name: "entries-table",
        components: {EntriesTableEntryRow},
        data: function(){
            return {
                entries: new Entries()
            }
        },
        computed: {
            listOfEntries: function(){
                return this.entries.retrieve;
            }
        }
    }
</script>

<style scoped>

</style>