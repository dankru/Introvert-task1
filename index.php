<?php

/*  Данный скрипт формирует таблицу с данными о суммированной прибыли от сделок, выполненных каждым валидным клиентом
 *
 * */

use core\Application;

require_once(__DIR__ . '/vendor/autoload.php');

$api = new Introvert\ApiClient();
$api->getConfig()->setHost('https://api.s1.yadrocrm.ru/tmp');

function getClients(): array
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
        [
            "id" => 8967010,
            "name" => "intrdev",
            "api" => "23bc075b710da43f0ffb50ff9e889aed"
        ],
    ];
}

function filterClientsByAccess($clients, $api): array
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

function getClientLeads($client, $api): array
{
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
    return $leads;
}

function filterLeadsByDate($leads, $dateFrom, $dateTo): array
{
    // returns filtered by date leads array
    return array_filter($leads["result"], fn($lead) => $lead["date_create"] > $dateFrom && $lead["date_close"] < $dateTo);
}

function getLeadsSum($filteredLeads): int
{
    // reduces leads array to sum, returns sum
    return array_reduce($filteredLeads, fn($sum, $lead) => $sum += $lead["price"]);
}

?>
<!doctype html>
<html lang="en" style="width: 100%; height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Homepage</title>
</head>
<body style="width: 100%; height: 100%;">
<div style="width: 100%; height: 100%; display:flex; justify-content: center; align-items: center;flex-direction: column">
    <table>
        <?php
        if (!empty ($_POST['dateFrom']) && !empty($_POST['dateTo'])) :
            $clients = getClients();
            $filteredClients = filterClientsByAccess($clients, $api);
            foreach ($filteredClients as $client):
                $leads = getClientLeads($client, $api);
                $dateFrom = strtotime($_POST['dateFrom']);
                $dateTo = strtotime($_POST['dateTo']);
                $filteredLeads = filterLeadsByDate($leads, $dateFrom, $dateTo);
                $sum = getLeadsSum($filteredLeads); ?>
                <tr>
                    <td>ID Клиента:<?= $client["id"]; ?></td>
                    <td>Имя Клиента:<?= $client["name"]; ?></td>
                    <td>Сумма сделок клиента:<?= $sum; ?></td>
                </tr>
            <?php endforeach; ?>
            От:
            <?php echo date('Y-m-d H:i', $dateFrom) ?>
            <br>
            До:
            <?php echo date('Y-m-d H:i', $dateTo) ?>
        <?php endif; ?>
        <form action="index.php" method="POST"
        ">
        <input type="datetime-local" required name="dateFrom" style="width: 500px;">
        <input type="datetime-local" required name="dateTo" style="width: 500px;">
        <button type="submit" style="width: 500px;">Получить данные</button>
        </form>
    </table>
</div>

</body>
</html>
