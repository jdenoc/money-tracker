<!DOCTYPE html>
<html lang="en">
<head>
    @component('head-component')
        Money Tracker | STATS
    @endcomponent
</head>
<body>
<div id="app-stats">
    <navbar page-name="stats"></navbar>
    <div class="columns is-gapless">
        <div class="column is-one-fifth">
            <stats-nav></stats-nav>
        </div>
        <div class="column is-three-fifths">
            <stats></stats>
        </div>

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
<script type="text/javascript" src="{{mix('vue/js/app-stats.js')}}"></script>
</body>
</html>