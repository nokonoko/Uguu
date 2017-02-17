<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Temp file hosting, Up to 150MB for 1 hour.">
    <title>Uguu Installer</title>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <!-- materialize -->
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="../css/ie.css"  media="screen,projection"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script>
        $( document ).ready(function(){
            $(".button-collapse").sideNav();
        })
    </script>
    <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>

<body>
    <nav>
        <div class="nav-wrapper blue-grey darken-1">
            <div class="container">
                <div class="col s12">
                    <a href="/" class="brand-logo">Uguu Installer</a>
                    <a href="#" data-activates="mobile-demo" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
                    <ul class="right hide-on-med-and-down">
                        <li><a href="https://github.com/nokonoko/uguu">Github</a></li>
                    </ul>
                    <ul class="side-nav" id="mobile-demo">
                        <li><a href="/?info">Info</a></li>
                        <li><a href="https://github.com/nokonoko/uguu">Github</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
<div class="page-header">
  <h1>Installation</h1>
</div>
<h2>About</h2>
Uguu.se source code, stores files and deletes after X amount of time.<br>


<h2>Todo</h2>
Restructure files.<br>
Make global config file.<br>
Probably a lot of things but I'm a lazy fuck, come with suggestions.<br>
<H2>Using the API</H2>
Leaving POST value 'name' empty will cause it to save using the original filename.
Leaving POST value 'randomname' empty will cause it to use original filename or custom name if 'name' is set to file.ext.<br><br>

Putting anything into POST value 'randomname' will cause it to return a random filename + ext (xxxxxx.ext).<br><br>

Putting a custom name into POST value 'name' will cause it to return a custom filename (yourpick.ext).<br><br>

E.g:<br><br>
<pre>
curl -i -F name=test.jpg -F file=@localfile.jpg http://uguu.se/api.php?d=upload (HTML Response)
curl -i -F name=test.jpg -F file=@localfile.jpg http://uguu.se/api.php?d=upload-tool (Plain text Response)
</pre>
This will probably get changed later since it's messy and unpractical.<br><br>

Contact<br>
neku@pomf.se or @Nekunekus.<br><br><hr><center>
<a class="btn btn-default" href="configcreate.php" role="button">Install Now</a>
</center>
</body>

</html>