<?php

$dir = __DIR__;
require_once $dir . '/../../lib.php';

$productsApi = $client->dbtech_ecommerce->product;

$products = $productsApi->getProducts();

$product = $productsApi->getProduct(364);

$latestVersion = $productsApi->getLatestVersion(364, 'xf21', 'full');

$purchases = $productsApi->getPurchases([5], ['xf21']);