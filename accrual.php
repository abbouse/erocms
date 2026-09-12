<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $mysqli = new mysqli('localhost', 'erocms', 'pass', 'erocms');

    if ($mysqli -> connect_error) {
        die('Error : ('. $mysqli -> connect_errno .') '. $mysqli -> connect_error);
    }

    mysqli_set_charset($mysqli, 'utf8');

    $WK_ID = 1;
    $WK_SECRET = 'erocms';

    require 'core/WapkassaClass.php';
    
    try {

    $wapkassa = new WapkassaClass($WK_ID, $WK_SECRET);
    
    if ($wapkassa -> ping($_POST)) {
        echo $wapkassa -> successPing();
    } else {

        $params = $wapkassa -> parseRequest($_POST);
        $params['site']; 
        $params['name']; 
        $params['colour']; 
        $params['term']; 
        $params['owner']; 

		$mysqli -> query("INSERT INTO ero_advertising SET site = '" . $params['add']['site'] . "', name = '" . $params['add']['name'] . "', colour = '" . $params['add']['colour'] . "', term = '" . $params['add']['term'] . "', owner = '" . $params['add']['owner'] . "'");
		
        echo $wapkassa -> successPayment();
        
    }
    
    } catch (Exception $e) {
        echo 'Ошибка: ' . $e -> getMessage() . PHP_EOL;
    }
    
    $mysqli -> close();