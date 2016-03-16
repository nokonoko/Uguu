<?php if(!class_exists('raintpl')){exit;}?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Temp file hosting, Up to 150MB for 1 hour.">
    <title>Uguu.se &middot; <?php echo $title;?></title>
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <!-- materialize -->
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="css/ie.css"  media="screen,projection"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script>
        $( document ).ready(function(){
            $(".button-collapse").sideNav();
        })
    </script>
</head>

<body>
    <nav>
        <div class="nav-wrapper blue-grey darken-1">
            <div class="container">
                <div class="col s12">
                    <a href="/" class="brand-logo">Uguu.se</a>
                    <a href="#" data-activates="mobile-demo" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
                    <ul class="right hide-on-med-and-down">
                        <li><a href="/?info">Info</a></li>
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
