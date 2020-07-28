<?php

include_once  'send_request_and_error_print.php';

define('TOKEN_FILE',  'tmp' . DIRECTORY_SEPARATOR . 'token_info.json');

$subdomain = 'gvaramin'; // Поддомен нужного аккаунта
$data_for_token = [
    'client_id' => '31bbeddb-9bdf-4293-932d-437b38c48b37',
    'client_secret' => 't4evwbi00BRL4QkDLYafJZ526xrXfGYerXTUk3jihMjO5pJAo4NOLFkFZ8CzGup4',
    'grant_type' => 'authorization_code',
    'code' => 'def50200d504f84eca16dbde4b0ad3ba2328742360a32424a64c51375c14ea1b33f688acb2cb002ad34d941fa614800b9bc5a9de6fe4aeae63d2a37ec8b0cad874c2388f41de3f56de9b65eef7be0f6331c09f40ea9cc390126374b8f74b3a187f12075c6195d108c967a017704f7f8d60e66baec5225063888dc9eea428acd1d33ec81a690520896bef6d6e2a36821745b00163e46e450fada865892060e6c3eb8e530d75245f448cd1843b30c22842e2d106998b173c2d83ed94f73efecd096ca290935c1ba627ecc4bf688c0f83679b4fcfe56f4f08cb28f20b7bff62d0d79e7aa4cb39e37b08d9d0413d158da3ddaacabcab2716763e01f3ea0928f99f2600d0d73176c6c856259ff31b1449651aebb0757e70a6041f210e52cf853b8a79da013d15a42d3c3af79abe47f1c3d806ff7ac1a30add5bf2c478ab2ba1fdb8a01da8d676a63c15b08779c084c017bb9816e70a5ce730d8432d05f2d393fb2163162d5c6e908a1e97b31681281b11df58b5980f32a11a0156b53eb92676f37feca99609bc84650ddd42473989938e89e1adc705460ae08170b3a8bf70120f74cbfeacca3ca50c8d8566f5401475c97fd36e597b7d9f072f88f70947bc9769660529e4bb0e485391ac',
    'redirect_uri' => 'http://http://2c071e047ba7.ngrok.io//',
]; //Соберем данные для запроса // преобразуем получившийся массив в JSON


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
    $data_for_token = json_encode($data_for_token);
    $access_token = get_token($data_for_token, $subdomain); //Access токен
} else{
    $accessToken = json_decode(file_get_contents(TOKEN_FILE), true);
    if ($accessToken['expires_in'] < time() + 60 ){
        $data_for_token['grant_type'] = 'refresh_token';
        $data_for_token['code'] = $accessToken['refresh_token'];
        $data_for_token = json_encode($data_for_token);
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









