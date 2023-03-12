<?php

if(!extension_loaded('gd')) {
   die('A extensão "gd" é necessária para gerar a notinha, e ela não foi encontrada na sua instalação PHP :(');
}


$productsCount = count($_POST['code']);
$isDownload = $_GET['download'] == 'true';

if($productsCount < 1) {
   header('Location: /');
   exit();
}

$products = [];
$purchaseTotal = 0;
$i = 0;


while($i < $productsCount) {
   $productCode        = $_POST['code'][$i];
   $productDescription = $_POST['description'][$i];
   $productPrice       = (float) $_POST['price'][$i];

   array_push($products, [
      'code' => $productCode,
      'description' => utf8_decode($productDescription),
      'price' => $productPrice
   ]);

   $i++;
   $purchaseTotal+=$productPrice;
}

$storeName    = utf8_decode($_POST['store-name']);
$storeAddress = utf8_decode($_POST['store-address']);
$storeCnpj    = utf8_decode($_POST['store-cnpj']);
$storePhone   = utf8_decode($_POST['store-phone']);
$receiptDate  = $_POST['receipt-date'];
$payedValue   = (float) $_POST['payed-value'];

try {
   $receiptDate = new DateTime($receiptDate);
} catch (Exception) {
   $receiptDate = false;
}

if($purchaseTotal > $payedValue) {
   die("Oops, a compra deu ruim pq o total foi {$purchaseTotal} e o valor pago foi menor, {$payedValue}");
}

require('./class/Receipt.php');
$receipt = new Receipt();

$receipt->setStoreName($storeName);
$receipt->setStoreAddress($storeAddress);
$receipt->setStoreCnpj($storeCnpj);
$receipt->setStorePhoneNumber($storePhone);

if($receiptDate) {
   $receipt->setReceiptDate($receiptDate);
}

$receipt->setPurchaseProducts($products);
$receipt->setPurchasePayedValue($payedValue);

$receipt->output($isDownload);
