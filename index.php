<?php

include_once  'send_request_and_error_print.php';

define('TOKEN_FILE',  'tmp' . DIRECTORY_SEPARATOR . 'token_info.json');

$subdomain = 'gvaramin'; // Поддомен нужного аккаунта
$data_for_token = json_encode([
    'client_id' => 'xxxxx',
    'client_secret' => 'xxxxx',
    'grant_type' => 'authorization_code',
    'code' => 'xxx',
    'redirect_uri' => 'http://xxx/',
]); //Соберем данные для запроса и преобразуем получившийся массив в JSON


/** Функция для записи задачи */
function task_creator($lead_id, $access_token, $subdomain){
    $jsonData = [array(
        "task_type_id"=> 1,
        "text"=> "Сделка без задачи",
        "complete_till"=> time() + 43200,
        "entity_id"=> $lead_id,
        "entity_type"=> "leads",
        "request_id"=> "example"
    )]; // Подготовка массива данных для дальнейшей записи в формате JSON
    echo $lead_id;
    $data = json_encode($jsonData); // Кодирование массива в JSON
    $options = array('access_token'=> $access_token, 'data'=>$data);
    list($out, $code) = send_request('task', $subdomain, $options);
    error_print($code);
    return json_decode($out, true);
}

/** Функция для получения токена с сервера */
function get_token($data_for_token, $subdomain){
    $options = array('data'=> $data_for_token); // Формируем массив заголовков и данных для запроса
    list($response, $code) = send_request('access', $subdomain, $options); //Отправляем запрос и получаем данные ответа
    error_print($code);// Обрабатываем ошибки
    file_put_contents(TOKEN_FILE, $response); // Запись токенов в хранилище
    return $response['access_token']; //Access токен
}

/** Получаем токены */
if (!file_exists(TOKEN_FILE)) {
    if (!file_exists('tmp')) { mkdir("tmp");} // Создаем директорию если она отсутствует
    $access_token = get_token($data_for_token, $subdomain); //Access токен
} else{
    $accessToken = json_decode(file_get_contents(TOKEN_FILE), true);
    if ($accessToken['expires_in'] < time() + 60 ){
        $data_for_token['grant_type'] = 'refresh_token';
        $data_for_token['code'] = $accessToken['refresh_token'];
        $access_token = get_token($data_for_token, $subdomain); //Access токен
    } else {
        $access_token = $accessToken['access_token']; // Получаем access_token из хранилища
    }
    unset($accessToken);
}


/** Получаем полный список сделок*/
$options = array('access_token'=> $access_token); // Формируем массив заголовков и данных для запроса
list($out, $code) = send_request('leads', $subdomain, $options); // Отправляем запрос и получаем данные ответа
error_print($code); // Обрабатываем ошибки
$response = json_decode($out, true); // Декодируем JSON
$leads_for_tasking = [];
foreach ($response["_embedded"]["leads"] as $lead){
    if(!$lead['closest_task_at'])
    {
        task_creator($lead['id'], $access_token, $subdomain);
    }
}

?>









