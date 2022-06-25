<?php

include "../vendor/autoload.php";
use Yufusphp\Vakifbank\Vakifbank;

$data = new Vakifbank();

$post = $data
    ->setMerchantId("")
    ->setMerchantPassword("")
    ->setTerminalNo("")
    ->setOrderId(rand())
    ->setCardNumber("")
    ->setExpiryDate("")
    ->setPurchaseAmount("")
    ->setCurrency("")
    ->setBrandName("")
    ->setSuccessUrl("")
    ->setFailureUrl("")
    ->check();

print_r($post);

