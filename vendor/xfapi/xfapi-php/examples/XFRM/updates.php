<?php

require_once '../lib.php';

$updateApi = $client->xfrm->update;

$updates = $updateApi->getUpdate(2);
