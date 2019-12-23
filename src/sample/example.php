<?php
namespace xingwenge\canal_php\sample;

use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\Fmt;

require_once __DIR__. '/../../vendor/autoload.php';

ini_set('display_errors', 'On');
error_reporting(E_ALL);

try {
    $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
    # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

    $client->connect("127.0.0.1", 11111);
    $client->checkValid();
    $client->subscribe("1001", "example", "dvuk.goods,dvuk.products");

    while (true) {
        $message = $client->get(100);
        if ($entries = $message->getEntries()) {
            foreach ($entries as $entry) {
//                Fmt::println($entry);
                Fmt::parseEntry($entry);
            }
        }
        sleep(1);
    }

    $client->disConnect();
} catch (\Exception $e) {
    echo $e->getMessage(), PHP_EOL;
}