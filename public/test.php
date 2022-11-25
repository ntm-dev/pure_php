<?php

require __DIR__ . "/../bootstrap/functions.php";
require __DIR__ . "/../vendor/autoload.php";

set_time_limit(0);

use App\Models\User;
use Core\Console\Command;

function genrerator()
{
    $chunkNum = 10000;
    $count = User::count();
    $i = 0;
    while(($i * $chunkNum) <= $count) {
        $users = User::take($chunkNum)->skip($i * $chunkNum)->get();
        foreach ($users as $user) {
            yield $user->toArray();
        }
        $i++;
    }
}

function downloadCsv()
{
    $data = genrerator();
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=a.csv');
    header('Content-Transfer-Encoding: Binary');
    header('Expires: 0');
    $handle = fopen("php://output", 'w');
    foreach ($data as $user) {
        fputcsv($handle, $user);
        flush();
        ob_flush();
    }
    fclose($handle);
}
$mem = memory_get_usage();
downloadCsv();
file_put_contents(__DIR__ . "/test.txt", (memory_get_usage() - $mem) . "\n" , FILE_APPEND);
die;

$command = new Command;

$user = User::first()->toArray();
unset($user['id']);

$startTime = microtime(true);

$insert = [];

for ($i=0; $i < 1000; $i++) {
    $insert[] = $user;
}
User::insert($insert);die;

$command->progressStart(6666);

for ($i=0; $i < 6666; $i++) {
    User::insert($insert);
    $command->progressAdvance();
}
$command->progressFinish();
$command->info("Execute time: " . (microtime(true) - $startTime));
