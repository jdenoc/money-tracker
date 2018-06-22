<template>
    <a class="account-node accordion-content panel-block" v-bind:id="'account-'+this.accountId">
        <span
            v-text="this.accountName"
            v-bind:class="{'badge is-badge-small is-badge-info is-badge-outlined': isAccountTotalVisable, 'tooltip is-tooltip-right is-tooltip-multiline' : hasAccountTypes}"
            v-bind:data-badge="'$'+parseFloat(accountTotal).toFixed(2)"
            v-bind:data-tooltip="accountTypeTooltipList"
        ></span>
    </a>
</template>

<script>
    import {Accounts} from "../accounts";

    export default {
        name: "institutions-panel-institution-account",
        props: ['id', 'name', 'total'],
        computed: {
            accountId: function(){
                return this.id;
            },
            accountName: function(){
                return this.name;
            },
            accountTotal: function(){
                return this.total;
            },
            isAccountTotalVisable: function(){
                return !isNaN(this.accountTotal);
            },
            accountTypeTooltipList: function(){
                let accountTypes = new Accounts().getAccountTypes(this.accountId);
                let tooltipList = "";
                accountTypes.forEach(function(accountType){
                    tooltipList += "- "+accountType.name+" ("+accountType.last_digits+")\n"
                });
                return tooltipList.trim();
            },
            hasAccountTypes: function(){
                let accountTypes = new Accounts().getAccountTypes(this.accountId);
                return accountTypes.length > 0;
            },
        },
        methods: {

        }
    }
</script>

<style scoped>
    li{
        padding-right: 65px;
    }
    .badge::after{
        margin: 9px 10px 0;
    }
    .account-node{
        background-color: #FFF;
        padding: 0.25em 0 0.25em 1.3em !important;
        text-decoration: none !important;
    }
    .tooltip:hover::after{
        display: none;
    }
    .is-tooltip-multiline::before{
        white-space: pre-line;
        width: 175px;
    }
</style>