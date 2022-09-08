<?php
Router::loadRequired();

try {

    ModificaPassword::updateLoggedUserPassword(
        Filters::email($_POST['email']?? ''),
        $_POST['old_pass']?? '',
        $_POST['new_pass']?? '',
        $_POST['repeat_pass']?? ''
    );

} catch (\Throwable $e) {

    if ($e->getCode() !== -1) {
        error_log((string)$e);
    }

} finally {

    header('Content-type: application/json;charset=utf-8');
    echo json_encode(['error' => isset($e)? $e->getMessage() : false]);

}