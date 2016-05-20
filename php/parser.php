<?php
session_start();

require_once('connection.php');
require_once('lib.php');
$filepath = $_FILES['file']['tmp_name'];
$user = R::load('user', $_SESSION['userid']);

require('lib/PHPExcel.php');
$objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
set_time_limit(60);

$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
$letters = range('A', 'Z');

if (isset($_GET['category'])) {
    $category = $_GET['category'];
    $attributes = $categories[$category];


    $ignoredAttributes = ['id', 'category', 'icon'];
    $attributes = array_values(array_diff($attributes, $ignoredAttributes));
    foreach ($sheetData as $row) {
        for ($i = 0; $i < sizeof($attributes); $i++) {
            $attribute = $attributes[$i];
            if (!in_array($attribute, $ignoredAttributes)) {
                if (isset($row[$letters[$i]])) {
                    $row[$attributes[$i]] = $row[$letters[$i]];
                }
            }

        }

        $object = R::dispense($category);
        $object->import($row, implode(",", $attributes));
        $object = encryptModel($object, $_SESSION['masterHash']);
        $user['own' . ucfirst($category) . 'List'][] = $object;
    }

    R::store($user);

    header("Location: ../index.php?category=" . $category);

}
