<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST;
}
if (!isset($data['username'], $data['password'] ) ) {
    die(json_encode(["error" => "Отсутствуют необходимые параметры"]));
}

$username = $data['username'];
$password = $data['password'];


$auth_url = "https://lknpd.nalog.ru/api/v1/auth/lkfl";
$auth_data = json_encode([
    "username" => $username,
    "password" => $password,
    "deviceInfo" => [
        "sourceDeviceId" => "*",
        "sourceType" => "WEB",
        "appVersion" => "1.0.0",
        "metaDetails" => [
            "userAgent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 YaBrowser/24.12.0.0 Safari/537.36"
        ]
    ]
]);

$options = [
    CURLOPT_URL => $auth_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json", "Accept: application/json, text/plain, */*"],
    CURLOPT_POSTFIELDS => $auth_data
];

$ch = curl_init();
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
curl_close($ch);

$auth = json_decode($response, true);
$token = $auth['token'] ?? null;

if (!$token) {
    die(json_encode(["error" => "Ошибка аутентификации"]));
}


$url = "https://lknpd.nalog.ru/api/v1/taxpayer";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json",
    "Accept: application/json, text/plain, */*",
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 YaBrowser/24.12.0.0 Safari/537.36"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
