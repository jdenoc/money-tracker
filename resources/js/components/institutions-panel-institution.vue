<template>
    <li v-bind:id="'institution-'+id" class="institution-panel-institution" v-bind:class="accordionClasses">
        <a class="institution-panel-institution-name"  v-on:click="toggleAccordion">
            <span class="panel-icon">
                <i v-bind:class="openCloseIcon" aria-hidden="true"></i>
            </span>
            <span class="name-label" v-text="name"></span>
        </a>

        <ul class="institution-panel-institution-accounts">
            <institutions-panel-institution-account
                v-for="account in activeAccountsInInstitution"
                v-bind:key="account.id"
                v-bind:id="account.id"
                v-bind:name="account.name"
                v-bind:accountCurrency="account.currency"
                v-bind:total="account.total"
            ></institutions-panel-institution-account>
        </ul>
    </li>
</template>

<script>
    import _ from 'lodash';
    import {Accounts} from '../accounts';
    import InstitutionsPanelInstitutionAccount from "./institutions-panel-institution-account";

    export default {
        name: "institutions-panel-institution",
        components: {InstitutionsPanelInstitutionAccount},
        props: {
            id: Number,
            name: String
        },
        data: function(){
            return {
                isOpen: false,
                openCloseIcons: {
                    opened: 'fas fa-chevron-up',
                    closed: 'fas fa-chevron-down'
                },
                accountsObject: new Accounts(),
            };
        },
        computed: {
            openCloseIcon: function(){
                return this.isOpen ? this.openCloseIcons.opened : this.openCloseIcons.closed;
            },
            closedAccountsOpenCloseIcon: function(){
                return this.isOpen ? this.openCloseIcons.closed : this.openCloseIcons.opened;
            },
            accordionClasses: function(){
                return this.isOpen ? '' : 'is-closed';
            },
            accountsInInstitution: function(){
                return this.accountsObject.retrieve.filter(function(account){
                    return account.institution_id === this.id;
                }.bind(this));
            },
            activeAccountsInInstitution: function(){
                return _.sortBy(
                    this.accountsInInstitution.filter(function(account){
                        return !account.disabled;
                    }),
                    'name'
                );
            }
        },
        methods:{
            toggleAccordion: function(){
                this.isOpen = !this.isOpen;
            },
        }
    }
</script>

<style lang="scss" scoped>
    .institution-panel-institution-accounts{
        max-height: 20rem;
        overflow: hidden;
        font-weight: 400;
        margin-top: 0;
        -webkit-transition: all 0.3s ease-in-out;
        -moz-transition: all 0.3s ease-in-out;
        -o-transition: all 0.3s ease-in-out;
        transition: all 0.3s ease-in-out;
    }

    .is-closed{
        .institution-panel-institution-accounts{
            max-height: 0;
        }
    }
</style>