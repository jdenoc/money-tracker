<template>
    <li v-bind:id="'account-'+id" class="institutions-panel-account has-background-white" v-tooltip="tooltipContent">
        <a class="has-text-info institutions-panel-account-name"
            v-on:click="displayAccountEntries"
            v-bind:class="{'is-active' : isAccountFilterActive}"
        >
        <span v-text="name"></span>
        <br/>
        <span class="account-currency" v-bind:class="{'is-hidden': !isAccountTotalVisible}">
            <i v-bind:class="accountCurrencyClass"></i>
            <span v-text="accountTotal"></span>
        </span>
        </a>
    </li>
</template>

<script>
    import {Accounts} from "../accounts";
    import {Currency} from '../currency';
    import Store from '../store';

    export default {
        name: "institutions-panel-institution-account",
        props: {
            id: Number,
            name: String,
            total: Number,
            accountCurrency: String,
            canShowTooltip: {
                type: Boolean,
                default: true
            }
        },
        data: function(){
            return {
                currencyObject: new Currency()
            }
        },
        computed: {
            accountTotal: function(){
                if(this.isAccountTotalVisible){
                    return this.total.toFixed(2);
                } else {
                    return '';
                }
            },
            accountCurrencyClass: function(){
                return this.currencyObject.getClassFromCode(this.accountCurrency);
            },
            isAccountFilterActive: function(){
                let currentFilter = Store.getters.currentFilter;
                return Object.keys(currentFilter).length === 1
                    && currentFilter.hasOwnProperty('account')
                    && currentFilter.account === this.id;
            },
            isAccountTotalVisible: function(){
                return !isNaN(this.total);
            },
            hasAccountTypes: function(){
                let accountTypes = new Accounts().getAccountTypes(this.id);
                return accountTypes.length > 0;
            },
            accountTypeTooltipList: function(){
                let accountTypes = new Accounts().getAccountTypes(this.id);
                let tooltipList = "";
                accountTypes.filter(function(accountType){
                    return accountType.hasOwnProperty('disabled') && !accountType.disabled;
                }).forEach(function(accountType){
                    tooltipList += "&bull; "+accountType.name+" ("+accountType.last_digits+")<br/>"
                });
                return tooltipList.trim();
            },
            tooltipContent: function(){
                return this.canShowTooltip && {
                    content: this.accountTypeTooltipList,
                    html: true,
                    placement: 'right',
                    classes: 'is-size-7 has-text-weight-semibold',
                }
            }
        },
        methods: {
            displayAccountEntries: function(){
                this.$eventHub.broadcast(this.$eventHub.EVENT_LOADING_SHOW);
                let filterDataParameters = {account: this.id};
                this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_TABLE_UPDATE, {pageNumber: 0, filterParameters: filterDataParameters});
            }
        }
    }
</script>

<style lang="scss" scoped>
    .institutions-panel-account{
        width: 100%;

        a.has-text-info.is-active{
            color: white !important;
        }

        .account-currency{
            font-weight: 900;
            font-size: 0.65rem;

            i{
                margin-right: -0.125rem;
            }
        }
    }
</style>