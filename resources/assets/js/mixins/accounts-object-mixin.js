import _ from 'lodash';
import {Accounts} from "../accounts";

export const accountsObjectMixin = {
    data: function(){
        return {
            accountsObject: new Accounts()
        }
    },

    computed: {
        rawAccountsData: function(){
            return this.accountsObject.retrieve;
        },
        areAccountsAvailable: function(){
            return !_.isEmpty(this.rawAccountsData);
        },
    }
};