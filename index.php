<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Функция для получения данных из запроса
function getRequestData() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            return json_decode(file_get_contents("php://input"), true) ?? $_POST;
        }
        return $_POST;
    } else {
        return $_GET;
    }
}

// Функция для аутентификации
function authenticate($username, $password) {
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
    return $auth['token'] ?? null;
}

// Функция для форматирования времени
function getFormattedTime() {
    $date = new DateTime("now", new DateTimeZone("Europe/Moscow"));
    return $date->format("Y-m-d\TH:i:sP");
}

// Основная логика
$data = getRequestData();
$method = $data['method'] ?? $_GET['method'] ?? '';

switch ($method) {
    case 'create_income':
        handleCreateIncome($data);
        break;
        
    case 'get_profile':
        handleGetProfile($data);
        break;
        
    case 'get_incomes':
        handleGetIncomes($data);
        break;
        
    case 'help':
    case '':
    default:
        showHelp();
        break;
}

function handleCreateIncome($data) {
    if (!isset($data['username'], $data['password'], $data['amount'], $data['comment'])) {
        echo json_encode(["error" => "Отсутствуют необходимые параметры: username, password, amount, comment"]);
        return;
    }

    $username = $data['username'];
    $password = $data['password'];
    $amount = (float)$data['amount'];
    $comment = $data['comment'];

    $token = authenticate($username, $password);
    if (!$token) {
        echo json_encode(["error" => "Ошибка аутентификации"]);
        return;
    }

    // Формирование данных для чека
    $formatted_time = getFormattedTime();

    $chek_data = json_encode([
        "operationTime" => $formatted_time,
        "requestTime" => $formatted_time,
        "services" => [
            [
                "name" => $comment,
                "amount" => $amount,
                "quantity" => 1
            ]
        ],
        "totalAmount" => (string)$amount,
        "client" => [
            "contactPhone" => null,
            "displayName" => null,
            "inn" => null,
            "incomeType" => "FROM_INDIVIDUAL"
        ],
        "paymentType" => "CASH",
        "ignoreMaxTotalIncomeRestriction" => false
    ]);

    // Отправка чека
    $chek_url = "https://lknpd.nalog.ru/api/v1/income";
    $chek_options = [
        CURLOPT_URL => $chek_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            "Content-Type: application/json",
            "Accept: application/json, text/plain, */*",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 YaBrowser/24.12.0.0 Safari/537.36"
        ],
        CURLOPT_POSTFIELDS => $chek_data
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $chek_options);
    $chek_response = curl_exec($ch);
    curl_close($ch);

    echo $chek_response;
}

function handleGetProfile($data) {
    if (!isset($data['username'], $data['password'])) {
        echo json_encode(["error" => "Отсутствуют необходимые параметры: username, password"]);
        return;
    }

    $username = $data['username'];
    $password = $data['password'];

    $token = authenticate($username, $password);
    if (!$token) {
        echo json_encode(["error" => "Ошибка аутентификации"]);
        return;
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
}

function handleGetIncomes($data) {
    if (!isset($data['username'], $data['password'])) {
        echo json_encode(["error" => "Отсутствуют необходимые параметры: username, password"]);
        return;
    }

    $username = $data['username'];
    $password = $data['password'];

    $token = authenticate($username, $password);
    if (!$token) {
        echo json_encode(["error" => "Ошибка аутентификации"]);
        return;
    }

    $url = "https://lknpd.nalog.ru/api/v1/incomes?offset=0&sortBy=operation_time:desc&limit=10";

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
}

function showHelp() {
    $response = [
        'api_name' => 'Moy Nalog API',
        'version' => '1.0',
        'description' => 'Единый API для взаимодействия с сервисом "Мой Налог"',
        'GitHub' => 'https://github.com/FSystem88/Moy-Nalog',
        'usage' => 'Используйте параметр method для выбора действия',
        'methods' => [
            'create_income' => [
                'description' => 'Создание нового дохода (чека)',
                'parameters' => [
                    'username' => 'string (обязательный) - Логин в ЛКНПД',
                    'password' => 'string (обязательный) - Пароль в ЛКНПД',
                    'amount' => 'float (обязательный) - Сумма дохода',
                    'comment' => 'string (обязательный) - Комментарий/название услуги'
                ],
                'examples' => [
                    'GET' => '/index.php?method=create_income&username=user&password=pass&amount=1000&comment=Услуги',
                    'POST' => '{"method":"create_income","username":"user","password":"pass","amount":1000,"comment":"Услуги"}'
                ]
            ],
            'get_profile' => [
                'description' => 'Получение информации о налогоплательщике',
                'parameters' => [
                    'username' => 'string (обязательный) - Логин в ЛКНПД',
                    'password' => 'string (обязательный) - Пароль в ЛКНПД'
                ],
                'examples' => [
                    'GET' => '/index.php?method=get_profile&username=user&password=pass',
                    'POST' => '{"method":"get_profile","username":"user","password":"pass"}'
                ]
            ],
            'get_incomes' => [
                'description' => 'Получение списка доходов за последнее время',
                'parameters' => [
                    'username' => 'string (обязательный) - Логин в ЛКНПД',
                    'password' => 'string (обязательный) - Пароль в ЛКНПД'
                ],
                'examples' => [
                    'GET' => '/index.php?method=get_incomes&username=user&password=pass',
                    'POST' => '{"method":"get_incomes","username":"user","password":"pass"}'
                ]
            ]
        ],
        'notes' => [
            'Можно использовать как GET, так и POST запросы',
            'Для POST запросов данные можно отправлять в JSON или form-data',
            'Все методы требуют аутентификации',
            'Временная зона: Europe/Moscow'
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>
