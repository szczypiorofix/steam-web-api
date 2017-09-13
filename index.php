<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- GOOGLE ANALYTICS SCRIPT -->
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments);},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m);
                })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
            ga('create', 'UA-84715211-1', 'auto');
            ga('send', 'pageview');
        </script>
        <meta charset="UTF-8">
        <title>Wróblewski Piotr - SteamWeb API test</title>
        <meta charset="utf-8">
        <META HTTP-EQUIV="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Wróblewski Piotr - SteamWeb API test">
        <meta name="author" content="Wróblewski Piotr">
        <meta property="og:title" content="Wróblewski Piotr">
        <meta property="og:image" content="https://wroblewskipiotr.pl/blackboard/icon.png">
        <meta property="og:description" content="Wróblewski Piotr - SteamWeb API test">
        <link rel="icon" href="icon.ico">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="maindiv">
            <img  src="steam_logo.png" alt="Steam Logo" height="60">
            <h3>SteamWeb API test:</h3>
            <div id = "spinner" class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
            <div id="contentDiv" class="animate-bottom">
            </div>  
        </div>
        
        <script>                
            var contentDiv = document.getElementById('contentDiv');
            var loaderDiv = document.getElementById('spinner');

            function launchIt() {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState === 4 && this.status === 200) {
                        contentDiv.style.display = 'block';
                        loaderDiv.style.display = 'none';
                        contentDiv.innerHTML = this.responseText;
                    }
                };
                xmlhttp.open("POST", "./getdata.php", true);
                xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xmlhttp.send("task=clear");
            }
            
           document.addEventListener("DOMContentLoaded", function(event) {
             launchIt();  
           });
        </script>
    </body>
</html>
