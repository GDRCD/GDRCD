<?php

require_once(__DIR__ . '/../required.php');

$cron = Cronjob::getInstance();

try {
    $cron->startCron();
} catch ( Throwable $e ) {
    die($e->getMessage());
}