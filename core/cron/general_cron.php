<?php

require_once(__DIR__ . '/../required.php');

$cron = Cronjob::getInstance();

$cron->startCron();