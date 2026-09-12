<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    require 'core/Functions.php';

    if (isset($_GET['func'])) $func = filter($_GET['func']);
    
    else 
    {

    require 'pages/default.php';
    
    foot();
    exit;
    
    }
    
	switch ($func) {
	
	#Страницы
	
    case $func:

    require 'pages/'.$func.'.php';
    
    break;

	}
	
	$mysqli -> close();
	
    foot();