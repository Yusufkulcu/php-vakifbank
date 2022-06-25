<?php

include "vendor/autoload.php";
use Yufusphp\Vakifbank\Vakifbank;

$data = new Vakifbank();
$post = $data
    ->setMerchantId("")
    ->setMerchantPassword("")
    ->setTerminalNo("")
    ->getPayment();
print_r($post);
