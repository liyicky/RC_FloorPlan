<?php
include 'Classes/PHPExcel.php';
include 'Classes/pdf2txt.php';

header('Content-Type: text/html; charset=utf-8');
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

    echo "<h2>ファイルが正常にアップロードされました.</h2>\n\n";

    //$python = "c:\Python27\python.exe";
    //$pdf2txt = "c:\Python27\Scripts\pdf2txt.py";
    //$command = escapeshellcmd("pdf2txt.py"." ".$pdf_file);
    $output = shell_exec("c:\Python27\python.exe c:\Python27\Scripts\pdf2txt.py"." ".$pdf_file);

    //$pdf2txt = new PDF2Text();
    //$pdf2txt->setFilename("");
    //$pdf2txt->decodePDF();
    //$output = $pdf2txt->output();
    parse($output, $pdf_file);



} catch (RuntimeException $e) {

    echo $e->getMessage();

}

function parse($txt, $pdf_file) {
  $rows = explode("\n", $txt);
  $line_counter = 1;

  foreach($rows as $row) {
    if ($line_counter == 3) { $line3 = $row; };
    if ($line_counter == 9) { $line9 = $row; };
    $line_counter++;
  }


  $line3= explode("所有者一覧表", $line3);
  $line9= explode("┃", $line9);
  $parsed_str_1 = str_replace("　", "", $line3[0]);
  $parsed_str_2 = explode("　│", $line9[1])[0];
  $parsed_str_3 = explode("　│", $line9[1])[1];

  $objPHPExcel = new PHPExcel();
  $objPHPExcel->getProperties()->setCreator("Real Creative");
  $objPHPExcel->getProperties()->setLastModifiedBy("Real Creative");
  $objPHPExcel->setActiveSheetIndex(0);
  $objPHPExcel->getActiveSheet()->SetCellValue('A1', '何');
  $objPHPExcel->getActiveSheet()->SetCellValue('B1', '何');
  $objPHPExcel->getActiveSheet()->SetCellValue('C1', '何');
  $objPHPExcel->getActiveSheet()->SetCellValue('D1', '地図');
  $objPHPExcel->getActiveSheet()->SetCellValue('A2', $parsed_str_1);
  $objPHPExcel->getActiveSheet()->SetCellValue('B2', $parsed_str_2);
  $objPHPExcel->getActiveSheet()->SetCellValue('C2', $parsed_str_3);
  $objPHPExcel->getActiveSheet()->SetCellValue('D2', googleMapsUrl($parsed_str_2));

  $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
  $objWriter->save(str_replace('.pdf', '.xlsx', $pdf_file));

}

function googleMapsUrl($address) {
  $urlSegment = "https://maps.googleapis.com/maps/api/staticmap?&zoom=17&size=400x400&key=AIzaSyCuSpiJ-P2QHmdCgtdJYwhGOnwp4TkXrtA&center=";
  return $urlSegment.$address;
}

?>

