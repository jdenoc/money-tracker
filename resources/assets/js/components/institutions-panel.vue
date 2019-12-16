<template>
    <nav class="panel has-background-white-bis">
        <p class="panel-heading">Institutions</p>

        <ul id="closed-accounts" class="menu-list" v-if="inactiveAccountsExist">
            <li v-bind:class="accordionClasses">
                <ul class="institution-panel-institution-accounts">
                    <institutions-panel-institution-account
                        v-for="account in inactiveAccounts"
                        v-bind:key="account.id"
                        v-bind:id="account.id"
                        v-bind:name="account.name"
                        v-bind:canShowTooltip="false"
                    ></institutions-panel-institution-account>
                </ul>
                <a v-on:click="toggleClosedAccountsAccordion">
                    <span class="panel-icon">
                        <i v-bind:class="closedAccountsOpenCloseIcon" aria-hidden="true"></i>
                    </span>
                    <span class="name-label">Closed Accounts</span>
                </a>
            </li>
        </ul>

        <ul class="menu-list">
            <li><a id="overview" class="has-text-weight-semibold"
               v-bind:class="{'is-active' : isOverviewFilterActive, 'has-text-info': !isOverviewFilterActive}"
               v-on:click="displayOverviewOfEntries"
            >
                Overview
                <!-- TODO: <span>(filtered)</span> should appear if "complex" filter has been engaged -->
            </a></li>
            <institutions-panel-institution
                v-for="institution in activeInstitutions"
                v-bind:key="institution.id"
                v-bind:id="institution.id"
                v-bind:name="institution.name"
            ></institutions-panel-institution>
        </ul>

    </nav>
</template>

<script>
    import {Accounts} from '../accounts';
    import {Institutions} from '../institutions';
    import InstitutionsPanelInstitution from "./institutions-panel-institution";
    import InstitutionsPanelInstitutionAccount from './institutions-panel-institution-account';
    import Store from '../store';

    export default {
        name: "institutions-panel",
        components: {
            InstitutionsPanelInstitution,
            InstitutionsPanelInstitutionAccount
        },
        data: function(){
            return {
                institutionsObject: new Institutions(),
                accountsObject: new Accounts(),

                isClosedAccountsAccordionOpen: false,
                openCloseIcons: {
                    opened: 'fas fa-chevron-up',
                    closed: 'fas fa-chevron-down'
                },
            }
        },
        computed:{
            activeInstitutions: function(){
                return this.institutionsObject.retrieve.filter(function(institution){
                    return institution.active;
                }).sort(function(a, b){
                    if (a.name < b.name)
                        return -1;
                    if (a.name > b.name)
                        return 1;
                    return 0;
                });
            },
            inactiveAccounts: function(){
                return this.accountsObject.retrieve.filter(function(account){
                    return account.disabled;
                }).sort(function(a, b){
                    if (a.name < b.name)
                        return -1;
                    if (a.name > b.name)
                        return 1;
                    return 0;
                });
            },
            closedAccountsOpenCloseIcon: function(){
                return this.isClosedAccountsAccordionOpen ? this.openCloseIcons.closed : this.openCloseIcons.opened;
            },
            accordionClasses: function(){
                return this.isClosedAccountsAccordionOpen ? '' : 'is-closed';
            },
            isOverviewFilterActive: function(){
                let currentFilter = Store.getters.currentFilter;
                return Object.keys(currentFilter).length === 0;
            },
            inactiveAccountsExist: function(){
                return Object.keys(this.inactiveAccounts).length !== 0;
            }
        },
        methods:{
            toggleClosedAccountsAccordion: function(){
                this.isClosedAccountsAccordionOpen = !this.isClosedAccountsAccordionOpen;
            },
            displayOverviewOfEntries: function(){
                this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, {pageNumber: 0, filterParameters: {}});
            },
            updateAccountRecords: function(){
                this.accountsObject.fetch().then(function(notification){
                    this.$eventHub.broadcast(this.$eventHub.EVENT_NOTIFICATION, notification);
                }.bind(this));
            }
        },
        created: function(){
            this.$eventHub.listen(this.$eventHub.EVENT_ACCOUNT_UPDATE, this.updateAccountRecords);
        }
    }
</script>

<style lang="scss" scoped>
    .panel{
        position: fixed;
        width: 25%;
        height: 92%;

        .panel-heading{
            font-weight: 600;
        }

        .menu-list{
            line-height: 1;

            li ul{
                margin-top: 0.25rem;
                margin-right: 0;
                padding-left: 0;
            }
        }

        #closed-accounts.menu-list {
            position: absolute;
            bottom: 0;

            li ul {
                margin-bottom: 0;
                padding-left: 0.25rem;
            }

            .institution-panel-institution-accounts{
                max-height: 20rem;
                transition: 0.3s ease all;
                overflow: hidden;
                font-weight: 400;
                margin-top: 0;
            }

            .is-closed{
                .institution-panel-institution-accounts{
                    max-height: 0;
                }
            }
        }
    }
</style>