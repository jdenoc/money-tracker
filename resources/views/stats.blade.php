<!DOCTYPE html>
<html lang="en">
<head>
    @component('head-component')
        Money Tracker | STATS
    @endcomponent
</head>
<body>
<div id="app-stats" class="py-16">
    <navbar page-name="stats"></navbar>
    <stats-nav></stats-nav>
    <div class="ml-80">
        <stats></stats>

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
<script type="text/javascript" src="{{mix('dist/js/app-stats.js')}}"></script>
</body>
</html>