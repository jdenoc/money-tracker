<template>
    <div class="container">
        <nav class="panel">
            <p class="panel-heading">Institutions</p>

            <a class="panel-block is-active">
                Overview
                <span class="is-filtered badge is-badge-small is-badge-outlined" data-badge="filtered"></span>
            </a>

            <section class="accordions">
                <institutions-panel-institution
                    v-for="institution in activeInstitutions"
                    v-bind:key="institution.id"
                    v-bind:id="institution.id"
                    v-bind:name="institution.name"
                ></institutions-panel-institution>
            </section>

                <!--<collapse-item title="Closed Accounts" class="panel-block">-->
                    <!--<ul>-->
                        <!--<institutions-pane-institution-account-->
                            <!--v-for="account in this.inactiveAccounts"-->
                            <!--v-bind:key="account.id"-->
                            <!--v-bind:id="account.id"-->
                            <!--v-bind:name="account.name"-->
                        <!--&gt;</institutions-pane-institution-account>-->
                    <!--</ul>-->
                <!--</collapse-item>-->
            <!--</collapse>-->
        </nav>
    </div>
</template>

<script>
    import {Institutions} from "../institutions";
    import {Accounts} from "../accounts";
    import InstitutionsPanelInstitution from "./institutions-panel-institution";

    export default {
        name: "institutions-panel",
        components: {
            InstitutionsPanelInstitution,
        },
        data: function(){
            return {
                institutions: new Institutions(),
            }
        },
        computed: {
            institutionsAreAvailable: function(){
                return this.institutions.retrieve.length > 0;
            },
            accountsAreAvailable: function(){
                return new Accounts().retrieve.length > 0;
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
    .is-filtered{
        display: none;
        margin: 0 0 5px 10px;
    }
</style>