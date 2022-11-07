<!DOCTYPE html>
<html lang="en">
<head>
    @component('head-component')
        Money Tracker | Settings
    @endcomponent
</head>
<body>
<div id="app-settings" class="py-16">
    <nav-bar page-name="settings"></nav-bar>
    <settings-nav></settings-nav>
    <div class="ml-80">
        <settings-display></settings-display>
{{--        <div v-hotkey="keymap">--}}

        <!--
        loading-modal must ALWAYS be on the bottom.
        This way if the loading-modal is active and another modal is active,
        then it will look like the loading-modal is still active.
        -->
        <loading-modal></loading-modal>
        <!-- notifications are the only exception -->
        <notification></notification>
    </div>
</div>
<script type="text/javascript" src="{{mix('dist/js/app-settings.js')}}"></script>
</body>
</html>