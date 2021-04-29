import json from '../../storage/app/json/currency.json';
import _ from 'lodash';

export class Currency {

    constructor(){
        this.currencyData = json;
        this.default = {
            "label": "dollarUs",
            "code": "USD",
            "class": "fas fa-dollar-sign",
            "html": "&dollar;"
        };
    }

    list(){
        return this.currencyData;
    }

    getClassFromCode(currencyCode){
        let currencyNode = this.currencyData.filter(function(currencyDatum){
            return currencyCode === currencyDatum.code;
        }).shift();
        if(_.isEmpty(currencyNode)){
            return this.default.class;
        } else {
            return currencyNode.class;
        }
    }

    getHtmlFromCode(currencyCode){
        let currencyNode = this.currencyData.filter(function(currencyDatum){
            return currencyCode === currencyDatum.code;
        }).shift();
        if(_.isEmpty(currencyNode)){
            return this.default.html;
        } else {
            return currencyNode.html;
        }
    }

}