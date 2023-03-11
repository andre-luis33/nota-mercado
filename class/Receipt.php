<?php

class Receipt {

   const RECEIPT_WIDTH = 400;
   const RECEIPT_MIN_HEIGHT = 270;

   const DESCRIPTION_MAX_LENGTH = 16;

   const BACKGROUND_COLOR = '#FFFFC9';
   const LINE_SEPARTOR = '-------------------------------------------';

   # store atributes
   private String $storeName;
   private String $storeAddress;
   private String $storeCnpj;
   private String $storePhoneNumber;
   
   # purchase attributes
   private Array $products;
   private Float $purchaseTotalValue;
   private Float $purchasePayedValue;
   private DateTime $receiptDate;


   public function setStoreName(String $storeName): void {
      $this->storeName = $storeName;
   }

   public function getStoreName(): String {
      return $this->storeName ?? '';
   }

   public function setStoreAddress(String $storeAddress): void {
      $this->storeAddress = $storeAddress;
   }

   public function getStoreAddress(): String {
      return $this->storeAddress ?? '';
   }

   public function setStoreCnpj(String $storeCnpj): void {
      $this->storeCnpj = $storeCnpj;
   }

   public function getStoreCnpj(): String {
      return $this->storeCnpj ?? '';
   }

   public function setStorePhoneNumber(String $phoneNumber): void {
      $this->storePhoneNumber = $phoneNumber;
   }

   public function getStorePhoneNumber(): string {
      return $this->storePhoneNumber;
   }

   public function setReceiptDate(DateTime $date) {
      $this->receiptDate = $date;
   }

   public function getReceiptDate(): DateTime {
      return $this->receiptDate;
   }

   public function productsArrayIsValid(Array $products): bool {
      $productsValidated = array_map(function($product) {
         return $product['code'] && $product['description'] && $product['price'];
      }, $products);

      return count($products) === count($productsValidated);
   }

   public function setPurchaseProducts(Array $products): void {
      if(!$this->productsArrayIsValid($products)) throw new \InvalidArgumentException('Invalid products array');
      $this->products = $products;
   }

   public function getPurchaseProducts(): array {
      return $this->products ?? [];
   }

   /**
    * This is a private method because it is supposed to be called by the `Receipt::printProducts`, wich calculates the total purchase value xD
    */
   private function setPurchaseTotalValue(Float $purchaseTotal): void {
      $this->purchaseTotalValue = $purchaseTotal;
   }

   public function getPurchaseTotalValue(): float {
      return $this->purchaseTotalValue ?? 0;
   }


   public function setPurchasePayedValue(Float $value): void {
      $this->purchasePayedValue = $value;
   }

   public function getPurchasePayedValue(): float {
      return $this->purchasePayedValue;
   }

   /**
    * This method prints the products list, calculates the total purchase and sets `Receipt::$purchaseTotal`
    * @param  GdImage  $img must be the result of the `imagecreate()` function
    * @param  int      $textColor must be the result of the `imagecolorallocate()` function
    * @param  int      $positionY must be the last `$positionY` used by the `imagestring()` function
    * @return int      the value of the `$positionY` after incremented so that after the method call, the position stays on track
   */
   public function printProducts($img, Int $textColor, Int $positionY): int {

      $purchaseTotal = 0;
      $products = $this->getPurchaseProducts();
      $i = 0;

      foreach($products as $product) {
         $i++;
         $code        = $product['code'];
         $description = $product['description'];
         $price       = (float) $product['price'];

         $purchaseTotal+=$price;
         $positionY+=20;

         imagestring($img, 5, 10, $positionY, $i, $textColor);
         imagestring($img, 5, 60, $positionY, $code, $textColor);

         imagestring($img, 5, $this->getPriceXOffset($price), $positionY, 'R$ '.$this->parseToMoney($price), $textColor);
         $positionY = $this->printDescriptionRecursive($img, $textColor, $positionY, $description);
      }

      $positionY+=10;
      imagestring($img, 5, 10, $positionY, self::LINE_SEPARTOR, $textColor);
      
      $positionY+=10;
      imagestring($img, 5, 10, $positionY, 'TOTAL', $textColor);
      imagestring($img, 5, $this->getPriceXOffset($purchaseTotal), $positionY, 'R$ '.$this->parseToMoney($purchaseTotal), $textColor);
      $this->setPurchaseTotalValue($purchaseTotal);
      return $positionY;
   }

   /**
    * This method uses recursion to print multiples lines according to `$description` text length. 
    * @param  GdImage  $img must be the result of the `imagecreate()` function
    * @param  int      $textColor must be the result of the `imagecolorallocate()` function
    * @param  int      $positionY must be the last `$positionY` used by the `imagestring()` function
    * @return int|self next position Y in case the entire `$description was printed`, or the function itself
    */
   public function printDescriptionRecursive($img, Int $textColor, Int $positionY, String $description) {
      if(strlen($description) > self::DESCRIPTION_MAX_LENGTH) {
         $descriptionStart = substr($description, 0, (self::DESCRIPTION_MAX_LENGTH - 1));
         $descriptionEnd   = substr($description, (self::DESCRIPTION_MAX_LENGTH - 1), (strlen($description) - 1));
         
         imagestring($img, 5, 130, $positionY, trim($descriptionStart), $textColor);
         return $this->printDescriptionRecursive($img, $textColor, $positionY + 20, $descriptionEnd);
      } else {
         imagestring($img, 5, 130, $positionY, trim($description), $textColor);
         return $positionY;
      }
   }

   public function getProductsHeight(): int {
      $products = $this->getPurchaseProducts();
      $height = 0;
      foreach($products as $product) {
         $productDescription = $product['description'];
         if(strlen($productDescription) <= self::DESCRIPTION_MAX_LENGTH) {
            $height+=20;
            continue;
         }

         $linesCount = strlen($productDescription) / (self::DESCRIPTION_MAX_LENGTH - 1);
         $currentDescriptionHeight = (int) ($linesCount*20);
         $height+=$currentDescriptionHeight;
      }  

      return $height;
   }
   
   public function output($isDownload = false): void {
      $requiredAttributes = [
         $this->getStoreName(), $this->getStoreAddress(), $this->getStoreCnpj(), $this->getPurchaseProducts()
      ];

      if(in_array('', $requiredAttributes)) throw new Exception('All store\'s attributes must be provided!');

      $receiptDate = $this->getReceiptDate() ? $this->getReceiptDate()->format('d/m/Y H:i:s') : date('d/m/Y H:i:s');
      $productsHeight = $this->getProductsHeight();

      $img = imagecreate(self::RECEIPT_WIDTH, (self::RECEIPT_MIN_HEIGHT + $productsHeight));
      
      // setting background color
      imagecolorallocate($img, 255, 255, 199);

      $textColor = imagecolorallocate($img, 0, 0, 0);

      //store name
      imagestring($img, 5, 10, 5, $this->getStoreName(), $textColor);
      
      //store address
      imagestring($img, 5, 10, 25, $this->getStoreAddress(), $textColor);
      
      //store cnpj
      imagestring($img, 5, 10, 45, 'CNPJ: '.$this->getStoreCnpj(), $textColor);
      
      // separator
      imagestring($img, 5, 10, 55, self::LINE_SEPARTOR, $textColor);
      
      //date/time of creation
      imagestring($img, 5, 10, 65, $receiptDate, $textColor);
      
      // separator
      imagestring($img, 5, 10, 75, self::LINE_SEPARTOR, $textColor);
      
      // receipt title
      imagestring($img, 20, 140, 85, 'CUPOM FISCAL', $textColor);
      
      // items title
      imagestring($img, 5, 10, 105, 'ITEM', $textColor);
      
      // items title
      imagestring($img, 5, 60, 105, 'CODIGO', $textColor);
      
      // items title
      imagestring($img, 5, 130, 105, 'DESCRICAO', $textColor);
      
      // items title
      imagestring($img, 5, 340, 105, 'VALOR', $textColor);
      
      // separator
      imagestring($img, 5, 10, 115, self::LINE_SEPARTOR, $textColor);

      // setting products
      $positionY = $this->printProducts($img, $textColor, 115);

      // setting payed value
      $payedValue = $this->getPurchasePayedValue();
      $purchaseTotalValue = $this->getPurchaseTotalValue();

      $positionY+=20;
      imagestring($img, 5, 10, $positionY, 'DINHEIRO', $textColor);
      imagestring($img, 5, $this->getPriceXOffset($payedValue), $positionY, 'R$ '.$this->parseToMoney($payedValue), $textColor);
      
      // seting exchange value
      $exchangeValue = $payedValue - $purchaseTotalValue;
      if($exchangeValue < 0) throw new Exception("Payed value must be higher or equal to the purchase total value! Purchase total: {$purchaseTotalValue} / Payed value: {$payedValue}");

      $positionY+=20;
      imagestring($img, 5, 10, $positionY, 'TROCO', $textColor);
      imagestring($img, 5, $this->getPriceXOffset($exchangeValue), $positionY, 'R$ '.$this->parseToMoney($exchangeValue), $textColor);
      
      // separator
      $positionY+=10;
      imagestring($img, 5, 10, $positionY, self::LINE_SEPARTOR, $textColor);
      
      // setting items count
      $positionY+=10;
      $purchaseItemsCount = count($this->getPurchaseProducts());
      imagestring($img, 5, 10, $positionY, 'ITEM(S) COMPRADO(S): '.$purchaseItemsCount, $textColor);
      
      // setting store phone
      $positionY+=20;
      imagestring($img, 5, 10, $positionY, 'TELEFONE: '.$this->getStorePhoneNumber(), $textColor);
      
      // setting receipt code hash
      $positionY+=20;
      imagestring($img, 5, 10, $positionY, 'CODIGO: '.$this->getReceiptCode(), $textColor);
      
      // outputting
      header('Content-Type: image/png');
      if($isDownload) {
         header('Content-Disposition: attachment; filename=notinha.png');
      }

      imagepng($img, null, 9);
      imagedestroy($img);
   }



   # helper methods
   public function parseToMoney($value): string { 
      return is_numeric($value) ? number_format($value, 2, ',', '.') : '';
   }

   public function getPriceXOffset(Float $price): int {
      if ($price < 99.99) {
         return 320;
      } else if ($price < 999.99) {
         return 312;
      } else if ($price < 9999.99) {
         return 295;
      } else {
         return 280;
      }
   }

   public function randomString(Int $length){
      $random = random_bytes($length);
      return bin2hex($random);
   }

   /**
    * This method has to return MD5, otherwise it will break the layout
    */
   public function getReceiptCode(): string {
      return md5($this->randomString(10));
   }
}