<template>
  <nav id="navbar" class="bg-black text-white fixed inset-x-0 top-0 max-h-16 z-10">
    <div class="max-w-full mx-auto px-2">

      <div class="flex justify-between">

        <!-- mobile sidebar button -->
        <button id="navbar-sm-sidebar-btn" class="md:hidden flex items-center ml-2" v-on:click="clickMobileSidebarButton">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
          </svg>
        </button>

        <!-- ---------------------------- -->

        <!-- Logo -->
        <div class="flex items-center">
          <img src="/imgs/logo-white.png" id="navbar-logo" alt="Logo: Money Tracker" class="py-1 px-2 max-h-16" />
        </div>

        <!-- ---------------------------- -->

        <div class="hidden md:flex items-center">
          <span class="flex" v-if="isHomePage">  <!-- home page modal buttons -->
            <button id="navbar-entry-modal" type="button" class="flex items-center px-3 py-2 cursor-pointer hover:bg-gray-900 rounded-lg" v-on:click="openAddEntryModal">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
              </svg>
              <span>Add Entry</span>
            </button>

            <button id="navbar-transfer-modal" type="button" class="flex items-center px-3 py-2 cursor-pointer hover:bg-gray-900 rounded-lg" v-on:click="openTransferModal">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
              </svg>
              <span>Add Transfer</span>
            </button>

            <button id="navbar-filter-modal" type="button" class="flex items-center px-3 py-2 cursor-pointer hover:bg-gray-900 rounded-lg" v-on:click="openFilterModal">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
              </svg>
              <span>Filter</span>
            </button>
          </span>

          <!-- overflow/profile menu button -->
          <button id="navbar-overflow-menu-btn" type="button" class="flex items-center px-3" v-on:click="clickNavbarOverflow">
            <img src="/imgs/profile-placeholder.png" alt="profile" class="profile-picture max-h-12 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>

          <!-- overflow menu -->
          <transition name="navbar-overflow-menu">
            <div id="navbar-overflow-menu" class="-mt-1 absolute bg-white divide-gray-300 divide-y duration-300 ease-in-out focus:outline-none origin-top-right right-0 ring-1 ring-black ring-opacity-5 rounded-md shadow-lg text-gray-700 text-sm top-16 w-56" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1"
                 v-show="hasNavbarOverflowBeenClicked"
            >
              <div class="profile-username py-1" role="none">
                <span class="block px-4 py-2 font-medium" role="menuitem" tabindex="-1" id="menu-item-0"><span v-text="username"></span></span>
              </div>
              <div class="app-version py-1" role="none">
                <span class="text-blue-400 block px-4 py-2 italic" role="menuitem" tabindex="-1" id="menu-item-1">Version: <span v-text="appVersion"></span></span>
              </div>
              <div class="py-1" role="none">
                <a href="/home" class="flex items-center px-4 py-2 hover:text-gray-900" role="menuitem" tabindex="-1" id="menu-item-2" v-if="!isHomePage">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                  </svg>
                  <span>Home</span>
                </a>
                <a href="/stats" class="flex items-center px-4 py-2 hover:text-gray-900" role="menuitem" tabindex="-1" id="menu-item-3" v-if="!isStatsPage">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                    <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                  </svg>
                  <span>Statistics</span>
                </a>
                <a href="/settings" class="flex items-center px-4 py-2 hover:text-gray-900" role="menuitem" tabindex="-1" id="menu-item-4" v-if="!isSettingsPage">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                  </svg>
                  <span>Settings</span>
                </a>
              </div>
              <div class="py-1" role="none">
                <a href="/logout" class="flex items-center  px-4 py-2 hover:text-gray-900" role="menuitem" tabindex="-1" id="menu-item-5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-px mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  <span>Logout</span>
                </a>
              </div>
            </div>
          </transition>

        </div>

        <!-- ---------------------------- -->

        <!-- mobile menu button -->
        <button id="navbar-sm-overflow-menu-btn" class="md:hidden flex items-center mr-2" v-on:click="clickNavbarOverflow">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>

      <!-- mobile menu -->
      <div id="navbar-sm-overflow-menu" class="md:hidden bg-white text-black divide-y divide-gray-400 divide-solid" v-show="hasNavbarOverflowBeenClicked">
        <div class="block pb-2 pl-4 pt-4 flex items-center font-medium">
          <img src="/imgs/profile-placeholder.png" alt="profile" class="profile-picture max-h-8 pr-2 rounded-full">
          <span class="profile-username" v-text="username"></span>
        </div>
        <div class="app-version text-blue-400 block p-2 pl-4 italic">Version: <span v-text="appVersion"></span></div>
        <!-- links -->
        <div class="block pl-4 py-1">
          <a href="/home" class="flex items-center py-1" v-if="!isHomePage">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            <span>Home</span>
          </a>
          <a href="/stats" class="flex items-center py-1" v-if="!isStatsPage">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
              <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
            </svg>
            <span>Statistics</span>
          </a>
          <a href="/settings" class="flex items-center py-1" v-if="!isSettingsPage">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
            </svg>
            <span>Settings</span>
          </a>
        </div>

        <!-- home modal buttons -->
        <div class="block pl-8 py-1" v-if="isHomePage">
          <button id="navbar-sm-entry-modal" class="flex items-center py-1" v-on:click="openAddEntryModal">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
            </svg>
            <span>Add Entry</span>
          </button>

          <button id="navbar-sm-transfer-modal" class="flex items-center py-1" v-on:click="openTransferModal">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span>Add Transfer</span>
          </button>

          <button id="navbar-sm-filter-modal" class="flex items-center py-1" v-on:click="openFilterModal">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
            </svg>
            <span>Filter</span>
          </button>
        </div>

        <a href="/logout" class="block flex items-center p-2 pl-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
          </svg>
          <span>Logout</span>
        </a>
      </div>

    </div>
  </nav>
</template>

<script lang="js">
import {Version} from '../version';
import _ from 'lodash';

export default {
  name: "navbar",
  props: {
    pageName: {type: String, required: true}
  },
  data: function(){
    return {
      isMobileSidebarVisible: false,
      navbarOverflowButtonClicked: false,
      version: new Version(),
    }
  },
  computed: {
    appVersion: function(){
      let appVersion = this.version.retrieve;
      return _.isEmpty(appVersion) ? this.defaultAppVersion : appVersion;
    },
    defaultAppVersion: function(){
      return 'x.y.z';
    },
    defaultUsername: function(){
      return 'USERNAME';
    },
    hasNavbarOverflowBeenClicked: function(){
      return this.navbarOverflowButtonClicked;
    },
    isHomePage: function(){
      return this.pageName === 'home';
    },
    isSettingsPage: function(){
      return this.pageName === 'settings';
    },
    isStatsPage: function(){
      return this.pageName === 'stats';
    },
    username: function(){
      // FIXME: figure out how to set/get a username
      return this.defaultUsername;
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
    clickMobileSidebarButton(){
      this.isMobileSidebarVisible = !this.isMobileSidebarVisible;
    },
    clickNavbarOverflow: function(){
      this.navbarOverflowButtonClicked = !this.navbarOverflowButtonClicked;
    },
  }
}
</script>

<style lang="scss" scoped>
.navbar-overflow-menu-enter-active,
.navbar-overflow-menu-leave-active{
  $transition-time: 100ms;

  -webkit-transition: all #{$transition-time} ease-in-out;
  -moz-transition: all #{$transition-time} ease-in-out;
  -o-transition: all #{$transition-time} ease-in-out;
  transition: all #{$transition-time} ease-in-out;
}

.navbar-overflow-menu-enter-from,
.navbar-overflow-menu-leave-to {
  transform: translateY(-5%);  // slide down
  opacity: 0;
}
</style>