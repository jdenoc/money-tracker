<template>
    <nav class="panel">
        <p class="panel-heading">Institutions</p>

        <a id="institution-overview-links" class="panel-block is-active">
            Overview
            <span class="is-filtered badge is-badge-small is-badge-outlined" data-badge="filtered"></span>
        </a>

        <section class="accordions">
            <institutions-panel-institution
                v-for="institution in activeInstitutions"
                v-bind:key="institution.id"
                v-bind:id="institution.id"
                v-bind:name="institution.name"
                v-bind:total="account.total"
            ></institutions-panel-institution>

            <div class="accordion" v-show="inactiveAccountsAreAvailable">
                <div class="panel-block accordion-header toggle institution-node">
                    <p>Closed Accounts</p>
                </div>
                <div class="accordion-body panel">
                    <institutions-panel-institution-account
                        v-for="account in inactiveAccounts"
                        v-bind:key="account.id"
                        v-bind:id="account.id"
                        v-bind:name="account.name"
                    ></institutions-panel-institution-account>
                </div>
            </div>
        </section>
    </nav>
</template>

<script>
    import {Accounts} from "../accounts";
    import {Institutions} from "../institutions";
    import InstitutionsPanelInstitution from "./institutions-panel-institution";
    import InstitutionsPanelInstitutionAccount from "./institutions-panel-institution-account";

    export default {
        name: "institutions-panel",
        components: {
            InstitutionsPanelInstitution,
            InstitutionsPanelInstitutionAccount
        },
        data: function(){
            return {
                accounts: new Accounts(),
                institutions: new Institutions(),
            }
        },
        computed: {
            institutionsAreAvailable: function(){
                return this.institutions.retrieve.length > 0;
            },
            accountsAreAvailable: function(){
                return this.accounts.retrieve.length > 0;
            },
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
            },
            inactiveAccountsAreAvailable: function(){
                return this.inactiveAccounts.length > 0;
            }
        },
        methods: {
            fireAccordionReadyEvent: function(){
                let accordionReadyEvent = new Event('DOMContentLoaded');
                document.dispatchEvent(accordionReadyEvent);
            }
        },
        watch: {
            institutionsAreAvailable: function(){
                this.fireAccordionReadyEvent();
            },
            accountsAreAvailable: function(){
                this.fireAccordionReadyEvent();
            }
        }
    }
</script>

<style scoped>
    .panel-block.is-active{
        color: #3273dc;
        border-left-width: 3px;
    }
    #institution-overview-links{
        background-color: white;
    }
    #institution-overview-links:hover{
        background-color: whitesmoke;
    }
    .is-filtered{
        display: none;
        margin: 0 0 5px 10px;
    }
    .institution-node{
        background-color: initial !important;
        color: #363636 !important;
        border-bottom: 0;
    }
    .panel-heading{
        font-weight: bold;
     }
</style>