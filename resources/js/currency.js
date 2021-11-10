import json from '../../storage/app/json/currency.json'

export class Currency {

    constructor(){
        this.currencyData = json;
        this.default = {
            "label": "dollarUs",
            "code": "USD",
            "class": "fas fa-dollar-sign"
        };
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

}