<template>
    <div v-bind:id="'institution-id-'+this.institutionId" class="accordion">
        <div class="institution-node panel-block accordion-header toggle">
            <p v-text="this.institutionName"></p>
        </div>
        <div class="accordion-body panel">
            <institutions-panel-institution-account
                v-for="account in activeInstitutionAccounts"
                v-bind:key="account.id"
                v-bind:id="account.id"
                v-bind:name="account.name"
                v-bind:total="account.total"
            ></institutions-panel-institution-account>
        </div>
    </div>
</template>

<script>
    import {Accounts} from "../accounts";
    import InstitutionsPanelInstitutionAccount from "./institutions-panel-institution-account";

    export default {
        name: "institutions-panel-institution",
        components: {
            InstitutionsPanelInstitutionAccount,
        },
        props: ['id', 'name'],
        data: function(){
            return {
                accounts: new Accounts(),
            }
        },
        computed: {
            institutionId: function(){
                return this.id;
            },
            institutionName: function(){
                return this.name;
            },
            activeAccounts: function(){
                return this.accounts.retrieve.filter(function(account){
                    return !account.disabled
                })
            },
            activeInstitutionAccounts: function(){
                return this.activeAccounts.filter(function(account){
                    if(account.hasOwnProperty('institution_id')){
                        return this.institutionId === account.institution_id;
                    }
                }.bind(this));
            },
        }
    }
</script>

<style scoped>
    .institution-node{
        background-color: initial !important;
        color: #363636 !important;
        border-bottom: 0;
    }
</style>