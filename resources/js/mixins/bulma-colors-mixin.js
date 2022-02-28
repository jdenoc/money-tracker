export const bulmaColorsMixin = {

    computed: {
        // values taken from https://bulma.io/documentation/customize/variables/#initial-variables
        colorBlack: function(){ return "hsl(0, 0%, 4%)"; },
        colorBlackBis: function(){ return "hsl(0, 0%, 7%)"; },
        colorBlackTer: function(){ return "hsl(0, 0%, 14%)"; },
        colorGreyDarker: function(){ return "hsl(0, 0%, 21%)"; },
        colorGreyDark: function(){ return "hsl(0, 0%, 29%)"; },
        colorGrey: function(){ return "hsl(0, 0%, 48%)"; },
        colorGreyLight: function(){ return "hsl(0,0%,71%)"; },
        colorGreyLighter: function(){ return "hsl(0,0%,86%)"; },
        colorGreyLightest: function(){ return "hsl(0,0%,93%)"; },
        colorWhiteTer: function(){ return "hsl(0, 0%, 96%)"; },
        colorWhiteBis: function(){ return "hsl(0, 0%, 98%)"; },
        colorWhite: function(){ return "hsl(0, 0%, 100%)"; },
        colorGreen: function(){ return "hsl(153, 53%, 53%)"; },
        colorTurquoise: function(){ return "hsl(171, 100%, 41%)"; },
        colorCyan: function(){ return "hsl(207, 61%, 53%)"; },
        colorBlue: function(){ return "hsl(229, 53%, 53%)"; },
        colorPurple: function(){ return "hsl(271, 100%, 71%)"; },
        colorRed: function(){ return "hsl(348, 86%, 61%)"; },
        colorOrange: function(){ return "hsl(14, 100%, 53%)"; },
        colorYellow: function(){ return "hsl(44, 100%, 77%)"; },

        colorDark: function(){ return this.colorGreyDarker; },
        colorDanger: function(){ return this.colorRed; },
        colorInfo: function(){ return this.colorCyan; },
        colorLight: function(){ return this.colorWhiteTer; },
        colorPrimary: function(){ return this.colorTurquoise; },
        colorSuccess: function(){ return this.colorGreen; },
        colorWarning: function(){ return this.colorYellow; },
    }
};