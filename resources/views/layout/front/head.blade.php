<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apprael Store</title>  
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/include/sidebar.css">
    <link rel="stylesheet" href="/css/include/profile.css">
    <link rel="stylesheet" href="/fonts/fontawesome-free-5.15.3-web/css/all.css">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 

   
</head>

<body>

    <div id="flex">   
        <div class="header-container">                 
             @include('layout.front.topnav')                 
            <div class="header">              
                @include('layout.front.mainnav') 
            </div>
        </div>  