<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Money Tracker | vue testing</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <script src="https://cdn.jsdelivr.net/npm/vue@2.1.3/dist/vue.js"></script>
        <link href="{{asset('vue/css/app.css')}}" rel="stylesheet" type="text/css">
    </head>
    <body>
        <h1>Vue testing</h1>
        <div id="app">
            <section class="section">
                <institutions-pane></institutions-pane>
            </section>
        </div>
        <script type="text/javascript" src="{{asset('vue/js/app.js')}}"></script>
    </body>
</html>
