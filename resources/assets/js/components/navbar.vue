<template>
    <nav class="navbar is-black is-transparent is-fixed-top" role="navigation" aria-label="dropdown navigation">
        <div class="navbar-brand">
            <span class="navbar-item">Money Tracker</span>
        </div>
        <div class="navbar-menu">
            <div class="navbar-end">
                <a class="navbar-item" v-on:click="clickAddEntry"><i class="fas fa-plus-circle"></i> Add Entry</a>
                <a class="navbar-item" v-on:click="clickFilter"><i class="fas fa-filter"></i> Filter</a>

                <div class="navbar-item has-dropdown"
                    v-bind:class="{'is-active': hasNavbarAvatarImageBeenClicked}"
                    v-on:click="clickNavbarAvatarImage"
                    >
                    <a class="navbar-link"><img src="imgs/profile-placeholder.jpeg" alt="AVATAR"/></a>

                    <div class="navbar-dropdown is-boxed is-right">
                        <div class="navbar-item">TODO: NAME</div>
                        <hr class="navbar-divider">
                        <div id="app-version" class="navbar-item has-text-info is-italic">Version: {{appVersion}}</div>
                        <hr class="navbar-divider">
                        <a class="navbar-item" href="stats"><i class="fas fa-chart-pie"></i> Statistics</a>
                        <a class="navbar-item" href="settings"><i class="fas fa-cog"></i> Settings</a>
                        <hr class="navbar-divider">
                        <a class="navbar-item" href="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
    import {Version} from '../version';

    export default {
        name: "navbar",
        data: function(){
            return {
                defaultAppVersion: 'x.y.z',
                navbarAvatarImageClicked: false,
                version: new Version(),
            }
        },
        computed: {
            hasNavbarAvatarImageBeenClicked: function(){
                return this.navbarAvatarImageClicked;
            },
            appVersion: function(){
                let appVersion = this.version.retrieve;//'x.y.z';
                return appVersion === '' ? this.defaultAppVersion : appVersion;
            }
        },
        methods: {
            clickAddEntry: function(){
                this.modalNotAvailable();
            },
            clickFilter: function(){
                this.modalNotAvailable();
            },
            clickNavbarAvatarImage: function(){
                this.navbarAvatarImageClicked = !this.navbarAvatarImageClicked;
            },
            modalNotAvailable: function(){
                alert("Modal not currently available");
            }
        }
    }
</script>

<style scoped>
    .navbar-brand .navbar-item{
        font-weight: bold;
        font-size: 20px;
    }
    .fas{
        padding-right: 5px;
    }
    .navbar-divider{
        margin: 3px 0;
    }
</style>