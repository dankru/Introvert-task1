<?php

use core\Application;

require_once(__DIR__ . '/vendor/autoload.php');

$apiKey = '';

Introvert\Configuration::getDefaultConfiguration()->setApiKey('key', $apiKey);

$api = new Introvert\ApiClient();
try {
    $result = $api->account->info();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling account->allStatuses: ', $e->getMessage(), PHP_EOL;
}


function getAllSuccessfullLeads($dateFrom, $dateTo) {
    $leads = getLeads();
}
function getInfo($api)
{
    $result = $api->account->info();
    return $result;
}
function getClients($api)
{
    $result = $api->yadro->getUsers();
    return $result;
}
function getLeads($api)
{
    $result = $api->lead->getAll();
    return $result;
}

getInfo($api);