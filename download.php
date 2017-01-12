<?php
    session_start();

    $file = $_SESSION['excelFile'];

    if(!file_exists($file)) die("I'm sorry, the file doesn't seem to exist.");

    $type = filetype($file);

    header("Content-type: $type");
    header("Content-Disposition: attachment;filename={$file}");
    header('Pragma: no-cache'); 
    header('Expires: 0');
    set_time_limit(0); 
    readfile($file);
?>