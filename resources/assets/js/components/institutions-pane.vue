<template>
    <div class="container">
        <nav class="panel">
            <p class="panel-heading">Institutions</p>

            <a class="panel-block is-active">
                Overview
                <span class="is-filtered badge is-badge-small is-badge-outlined" data-badge="filtered"></span>
            </a>

            <collapse accordion is-fullwidth>
                <institutions-pane-institution
                    v-for="institution in activeInstitutions"
                    v-bind:key="institution.id"
                    v-bind:id="institution.id"
                    v-bind:name="institution.name"
                    ></institutions-pane-institution>

                <collapse-item title="Closed Accounts" class="panel-block">
                    <ul>
                        <institutions-pane-institution-account
                            v-for="account in this.inactiveAccounts"
                            v-bind:key="account.id"
                            v-bind:id="account.id"
                            v-bind:name="account.name"
                        ></institutions-pane-institution-account>
                    </ul>
                </collapse-item>
            </collapse>
        </nav>
    </div>
</template>

<script>
    import {Institutions} from "../institutions";
    import {Accounts} from "../accounts";
    import { Collapse, Item as CollapseItem  } from 'vue-bulma-collapse';
    import InstitutionsPaneInstitution from "./institutions-pane-institution";
    import InstitutionsPaneInstitutionAccount from "./institutions-pane-institution-account";

    export default {
        name: "institutions-pane",
        components: {
            InstitutionsPaneInstitutionAccount, InstitutionsPaneInstitution,
            Collapse, CollapseItem,
        },
        data: function(){
            return {
                displayed: false,
                institutions: new Institutions(),
                accounts: new Accounts(),
                // TODO: remove test data
                demoInstitutions: [
                    {id: 1, name: "xxx", active: true},
                    {id: 2, name: "yyy", active: true},
                    {id: 3, name: "zzz", active: true},
                    {id: 4, name: "abc", active: false}
                ],
                demoAccounts: [
                    {id: 1, name: 'aaa', total: 0.01, disabled: false},
                    {id: 2, name: 'bbb', total: 0.10, disabled: true},
                    {id: 3, name: 'ccc', total: 1.00, disabled: true},
                ],
            }
        },
        computed: {
            activeInstitutions: function(){
                return this.institutions.retrieve.filter(function(institution){
                    return institution.active;
                });
            },
            inactiveInstitutions: function(){
                return this.institutions.retrieve.filter(function(institution){
                    return !institution.active;
                });
            },
            inactiveAccounts: function(){
                return this.accounts.retrieve.filter(function(account){
                    return account.disabled
                })
            }
        }
    }
</script>

<style scoped>
    .is-filtered{
        display: none;
        margin: 0 0 5px 10px;
    }
</style>