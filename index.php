<?php

use core\Application;

require_once(__DIR__ . '/vendor/autoload.php');

$api = new Introvert\ApiClient();
$api->getConfig()->setHost('https://api.s1.yadrocrm.ru/tmp');


function getAllSuccessfullLeads($dateFrom, $dateTo, $api)
{
    $clients = getClients();
    $filteredClients = filterClientsByAccess($clients, $api);

    foreach ($filteredClients as $client) {
        // set apiKey of current client
        $api->getConfig()->setApiKey('key', $client["api"]);
        $leads = [];
        $offset = 0;
        $limit = 100;
        do {
            // get limited amount of leads and push to resulting leads array
            $leadsByPage = $api->lead->getAll($client["id"], 142, null, null, $limit, $offset);
            $leads = array_merge_recursive($leads, $leadsByPage);
            $count = count($leadsByPage["result"]);
            $offset += $limit;
        } while ($count >= $limit);
        // Leads ready to be filtered by date

        $filteredLeads = array_filter($leads["result"], fn($lead) => $lead["date_create"] > $dateFrom && $lead["date_close"] < $dateTo);
        echo '<pre>';
        foreach ($filteredLeads as $lead) {
            var_dump($lead);
        }
        echo '</pre>';
        $leadSum = array_reduce($filteredLeads, fn($sum, $lead) => $sum += $lead["price"]);
        echo $leadSum;
    }

}

// Нужно отфильтровать клиентов на только активных

function getClients()
{
    return [
        [
            "id" => 8967010,
            "name" => "intrdev",
            "api" => "23bc075b710da43f0ffb50ff9e889aed"
        ],
        [
            "id" => 2,
            "name" => "artedegrass0",
            "api" => "",
        ],
    ];
}

function filterClientsByAccess($clients, $api)
{
    $filteredClients = [];
    foreach ($clients as $client) {
        $apiKey = $client['api'];
        $api->getConfig()->setApiKey('key', $apiKey);
        try {
            $result = $api->account->info();
            array_push($filteredClients, $client);
        } catch (Exception $e) {

        }
    }
    return $filteredClients;
}

function getClientLeads()
{

}

$dateFrom = 1;
$dateTo = 111111111111111111;
echo getAllSuccessfullLeads($dateFrom, $dateTo, $api);