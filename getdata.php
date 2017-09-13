<?php

    if (isset($_POST['task'])) {
        //echo '<h3>OK!</h3>';
        require "./app.php";
        require "./steamwebapiapp.php";
        require "./steamwebuser.php";
        App::init();
        App::showOutput();
    }
    else {
        echo '<h3>Uuups! Something went wrong...</h3>';
    }