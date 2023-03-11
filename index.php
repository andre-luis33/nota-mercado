<?php 

error_reporting(E_ERROR);
date_default_timezone_set('America/Sao_Paulo');

$requestMethod = $_SERVER['REQUEST_METHOD'];
if($requestMethod === 'GET') {
   require('./public/form.html'); 
} else {
   require('./controller/receipt.php');
}

?>