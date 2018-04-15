<template>
    <a class="panel-block" v-bind:id="'institution-id-' + this.institutionId">
        <collapse-item v-bind:title="this.institutionName">
            <ul>
                <institutions-pane-institution-account
                    v-for="account in activeInstitutionAccounts"
                    v-bind:key="account.id"
                    v-bind:id="account.id"
                    v-bind:name="account.name"
                    v-bind:total="account.total"
                ></institutions-pane-institution-account>
            </ul>
        </collapse-item>
    </a>
</template>

<script>
    import { Item as CollapseItem } from 'vue-bulma-collapse'
    import {Accounts} from "../accounts";
    import InstitutionsPaneInstitutionAccount from "./institutions-pane-institution-account";

    export default {
        name: "institution-pane-institution",
        components: {
            InstitutionsPaneInstitutionAccount,
            CollapseItem,
        },
        props: ['id', 'name'],
        data: function(){
            return {
                displayed: false,
                accounts: new Accounts(),
                // TODO: remove test data
                demoAccounts: [
                    {id: 1, name: 'xxx', total: 0.01, disabled: false},
                    {id: 2, name: 'yyy', total: 0.10, disabled: false},
                    {id: 3, name: 'zzz', total: 1.00, disabled: false},
                ]
            }
        },
        computed: {
            institutionId: function(){
                return this.id;
            },
            institutionName: function(){
                return this.name;
            },
            inactiveAccounts: function(){
                return this.accounts.retrieve.filter(function(account){
                    return account.disabled
                })
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

</style>


