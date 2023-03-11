<?php

echo '<pre>'.print_r($_POST)."</pre>";
die;

require './src/Receipt.php';

$receipt = new Receipt();


$receipt->setStoreName('RENNER LTDA');
$receipt->setStoreAddress('AV. DAS AMERICAS 4099 - LOJA A');
$receipt->setStoreCnpj('45.543.915/0206-11');
$receipt->setStorePhoneNumber('(21) 2544-0772');

$receipt->setPurchaseProducts([
   [
      'code' => 66,
      'description' => 'MEIA PRETA',
      'price' => 19.90
   ],

   [
      'code' => 66,
      'description' => 'MEIA PRETA',
      'price' => 19.90
   ],
   [
      'code' => 66,
      'description' => 'MEIA PRETA',
      'price' => 19.90
   ],
   [
      'code' => 66,
      'description' => 'MEIA PRETA',
      'price' => 19.90
   ],
   [
      'code' => 349,
      'description' => 'ANDRE LUIS DOS SANTOS MOURA DA CUNHA',
      'price' => 1969.90
   ],
   [
      'code' => 1033,
      'description' => 'CALCA JOGGER',
      'price' => 114.90
   ]
]);

$receipt->setPurchasePayedValue(2500);
$receipt->output();
