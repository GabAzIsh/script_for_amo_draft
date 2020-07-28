<?php

function send_request($type, $subdomain, $options){
    /**
     * Нам необходимо инициировать запрос к серверу.
     * Воспользуемся библиотекой cURL (поставляется в составе PHP).
     * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
     */
    $common_link = 'https://' . $subdomain . '.amocrm.ru/';

    $curl = curl_init(); //Сохраняем дескриптор сеанса cURL

    if ($type == 'access')
    {
        $link = $common_link.'oauth2/access_token'; //Формируем URL для запроса
        $headers = ['Content-Type:application/json'];
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS, $options['data']);

    } elseif ($type == 'task')
    {
        $link = $common_link.'api/v4/tasks'; //Формируем URL для запроса
        $headers = ['Content-Type:application/json', 'Authorization: Bearer ' . $options['access_token']];
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $options['data']);

    } elseif ($type == 'leads'){
        $link = $common_link.'api/v4/leads'; //Формируем URL для запроса
        $headers = ['Authorization: Bearer ' . $options['access_token']];
    }
    /** Общие настройки */
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
    curl_setopt($curl,CURLOPT_URL, $link);
    curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl,CURLOPT_HEADER, false);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
    $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return array($out, $code);
}


function error_print($code){
    $code = (int)$code;
    $errors = [
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
    ];

    try
    {
        /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
        if ($code < 200 || $code > 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
        }
    }
    catch(Exception $e)
    {
        var_dump($e);
        die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());

    }
}



