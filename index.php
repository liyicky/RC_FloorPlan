<?php

header('Content-Type:  html; charset=utf-8');
//include("templates/header.html");
//include("templates/index.html");
//include("templates/footer.html");

try {

    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['upfile']['error']) ||
        is_array($_FILES['upfile']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['upfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here.
    if ($_FILES['upfile']['size'] > 1000000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['upfile']['tmp_name']),
        array(
            'pdf' => 'application/pdf'
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    $pdf_file = sprintf('.uploads/%s.%s',
        sha1_file($_FILES['upfile']['tmp_name']),
        $ext
    );
    if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $pdf_file)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    echo "File is uploaded successfully.\n\n";

    //$python = "c:\Python27\python.exe";
    //$pdf2txt = "c:\Python27\Scripts\pdf2txt.py";
    $command = escapeshellcmd("pdf2txt.py"." ".$pdf_file);
    $output = shell_exec("pdf2txt.py"." ".$pdf_file);
    parse($output);


} catch (RuntimeException $e) {

    echo $e->getMessage();

}

function parse($txt) {
  $rows = explode("\n", $txt);
  $line_counter = 1;

  foreach($rows as $row) {
    if ($line_counter == 3) { $line3 = $row; };
    if ($line_counter == 9) { $line9 = $row; };
    $line_counter++;
  }


  $line3= split("所有者一覧表", $line3);
  $line9= split("┃", $line9);
  $parsed_str_1 = str_replace("　", "", $line3[0]);
  $parsed_str_2 = split("　│", $line9[1])[0];
  $parsed_str_3 = split("　│", $line9[1])[1];

  echo $parsed_str_1;
  echo "\n";
  echo $parsed_str_2;
  echo "\n";
  echo $parsed_str_3;
  echo "\n";


}

?>

