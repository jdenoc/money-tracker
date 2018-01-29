<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    {{--<!--  TODO - <link rel="shortcut icon" href="../../assets/ico/favicon.ico">-->--}}
    <title>Money Tracker | HOME</title>

    <!-- Bootstrap core CSS -->
    <link href="{{asset('components/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('css/loading.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('css/dashboard.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('css/custom-bootstrap.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('css/institutions-pane.css')}}" rel="stylesheet" type="text/css"/>

    <script type="text/javascript" src="{{asset('components/jquery/dist/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('components/bootstrap/dist/js/bootstrap.min.js')}}"></script>

    <link href="{{asset('components/toastr/build/toastr.min.css')}}" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="{{asset('components/toastr/build/toastr.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/notice.js')}}"></script>

    <script type="text/javascript" src="{{asset('js/loading.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/paginate.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/home.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/institutions-pane.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/accounts-pane.js')}}"></script>
</head>
<body>

<!-- Top Nav Bar -->
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <!-- TODO - create logo -->
            <a class="navbar-brand" href="#">Money Tracker</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#" data-toggle="modal" data-target="#entry-modal" id="entry_add">Add Entry</a></li>
                <li><a href="#" data-toggle="modal" data-target="#filter-modal">Filter</a></li>
                <li><a href="#" data-toggle="dropdown" id="user-menu"><img src="/imgs/profile-placeholder.jpeg" alt="TODO - name required from session" class="img-circle" /></a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="user_menu">
                        <li role="presentation" class="dropdown-header">TODO - name required from session</li>
                        <li role="presentation" class="divider"></li>
                        <li><a href="stats"><span class="glyphicon glyphicon-stats"></span> Statistics</a></li>
                        <li><a href="settings"><span class="glyphicon glyphicon-cog"></span> Settings</a></li>
                        <li role="presentation" class="divider"></li>
                        <li><a href="logout"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- END - Top Nav Bar -->

<!-- Main body -->
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <ul id="institution-display-pane" class="nav nav-sidebar">
                <li><h4>Institutions</h4></li>
                <li id="entry-overview" class="active">
                    <a >Overview <span class="is-filtered">(filtered)</span></a>
                </li>
                <li id="closed-accounts-parent" class="panel panel-default">
                    <div id="closed-accounts" class="collapse panel-body institutions-pane-collapse"></div>
                    <a data-toggle="collapse" data-target="#closed-accounts" data-parent="#institution-display-pane" class="panel-heading">
                        <span class="panel-title">Closed Accounts <span class="glyphicon glyphicon-plus-sign"></span></span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <div class="table-responsive">
                <table id="entries-display-pane" class="table table-striped table-hover table-condensed">
                    <thead><tr>
                        <th></th>
                        <th>Date</th>
                        <th>Memo</th>
                        <th class="value-col">Income</th>
                        <th class="value-col">Expense</th>
                        <th class="type-col">Type</th>
                        <th><span class="glyphicon glyphicon-paperclip"></span></th>
                        <th><span class="glyphicon glyphicon-tags"></span></th>
                    </tr></thead>
                    <tbody></tbody>
                </table>
                <button type="button" class="btn btn-default" id="prev"><span class="glyphicon glyphicon-chevron-left"></span></button>
                <button type="button" class="btn btn-default" id="next"><span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>
    </div>
</div>
<!-- END - Main body -->

@include('modal.filter')
@include('modal.entry')

</body>
</html>