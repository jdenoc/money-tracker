<template>
    <nav class="navbar is-black is-transparent is-fixed-top" role="navigation" aria-label="dropdown navigation">
        <div class="navbar-brand">
            <span class="navbar-item"><img src="imgs/logo-white.png" alt="Money Tracker" /></span>

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false"
               v-bind:class="{'is-active': hasNavbarBurgerMenuBeenClicked}"
               v-on:click="clickNavbarBurger"
            >
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>
        <div class="navbar-menu" v-bind:class="{'is-active': hasNavbarBurgerMenuBeenClicked}">
            <div class="navbar-end">
                <a id="nav-entry-modal" class="navbar-item"
                   v-on:click="openAddEntryModal"
                   v-show="isHomePage"
                ><i class="fas fa-plus-circle"></i> Add Entry</a>
                <a id="nav-transfer-modal" class="navbar-item"
                   v-on:click="openTransferModal"
                   v-show="isHomePage"
                ><i class="fas fa-exchange-alt"></i> Add Transfer</a>
                <a id="nav-filter-modal" class="navbar-item"
                   v-on:click="openFilterModal"
                   v-show="isHomePage"
                ><i class="fas fa-filter"></i> Filter</a>

                <div class="navbar-item has-dropdown"
                    v-bind:class="{'is-active': hasNavbarAvatarImageBeenClicked}"
                    v-on:click="clickNavbarAvatarImage"
                    >
                    <a id="profile-nav-link" class="navbar-link is-hidden-touch"><img src="imgs/profile-placeholder.jpeg" alt="AVATAR"/></a>

                    <div class="navbar-dropdown is-boxed is-right">
                        <div class="navbar-item is-hidden-touch">TODO: NAME</div>
                        <hr class="navbar-divider">
                        <div id="app-version" class="navbar-item has-text-info is-italic is-hidden-touch">Version: {{appVersion}}</div>
                        <hr class="navbar-divider">
                        <a class="navbar-item" href="/" v-show="!isHomePage"><i class="fas fa-home"></i> Home</a>
                        <a class="navbar-item" href="stats" v-show="!isStatsPage"><i class="fas fa-chart-pie"></i> Statistics</a>
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
        props: {
            pageName: {type: String, required: true}
        },
        data: function(){
            return {
                defaultAppVersion: 'x.y.z',
                navbarAvatarImageClicked: false,
                navbarBurgerClicked: false,
                version: new Version(),
            }
        },
        computed: {
            isHomePage: function(){
                return this.pageName === 'home';
            },
            isStatsPage: function(){
                return this.pageName === 'stats';
            },
            hasNavbarAvatarImageBeenClicked: function(){
                return this.navbarAvatarImageClicked;
            },
            hasNavbarBurgerMenuBeenClicked: function(){
                return this.navbarBurgerClicked;
            },
            appVersion: function(){
                let appVersion = this.version.retrieve;//'x.y.z';
                return appVersion === '' ? this.defaultAppVersion : appVersion;
            }
        },
        methods: {
            openAddEntryModal: function(){
                this.$eventHub.broadcast(this.$eventHub.EVENT_ENTRY_MODAL_OPEN);
            },
            openFilterModal: function(){
                this.$eventHub.broadcast(this.$eventHub.EVENT_FILTER_MODAL_OPEN);
            },
            openTransferModal: function(){
                this.$eventHub.broadcast(this.$eventHub.EVENT_TRANSFER_MODAL_OPEN);
            },
            clickNavbarAvatarImage: function(){
                this.navbarAvatarImageClicked = !this.navbarAvatarImageClicked;
            },
            clickNavbarBurger: function(){
                this.navbarBurgerClicked = !this.navbarBurgerClicked;
            }
        }
    }
</script>

<style scoped>
    .navbar-brand .navbar-item{
        padding: 5px 16px;
    }
    .navbar-item img{
        max-height: 2.25rem;
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