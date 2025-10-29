# Moy Nalog API

PHP API для взаимодействия с сервисом "Мой Налог" (ЛКНПД)

## Методы API

### 📊 Получить профиль налогоплательщика
```
GET /index.php?method=get_profile&username=YOUR_USERNAME&password=YOUR_PASSWORD
```

```json
POST /index.php
{
  "method": "get_profile",
  "username": "YOUR_USERNAME",
  "password": "YOUR_PASSWORD"
}
```

### 💰 Создать доход (чек)
```
GET /index.php?method=create_income&username=USER&password=PASS&amount=1000&comment=Услуги
```

```json
POST /index.php
{
  "method": "create_income",
  "username": "YOUR_USERNAME",
  "password": "YOUR_PASSWORD",
  "amount": 1000,
  "comment": "Название услуги"
}
```

### 📈 Получить список доходов
```
GET /index.php?method=get_incomes&username=USER&password=PASS
```

```json
POST /index.php
{
  "method": "get_incomes",
  "username": "YOUR_USERNAME",
  "password": "YOUR_PASSWORD"
}
```

### ℹ️ Получить справку по API
```
GET /index.php
GET /index.php?method=help
```

## Примеры использования

### cURL
```bash
# Получить профиль
curl "https://lknpd-nalog.fuckdev.ru?method=get_profile&username=YOUR_USERNAME&password=YOUR_PASSWORD"

# Создать доход
curl -X POST https://lknpd-nalog.fuckdev.ru \
  -H "Content-Type: application/json" \
  -d '{"method":"create_income","username":"YOUR_USERNAME","password":"YOUR_PASSWORD","amount":1500,"comment":"Консультация"}'
```

### JavaScript
```javascript
// Получить профиль
fetch('https://lknpd-nalog.fuckdev.ru?method=get_profile&username=YOUR_USERNAME&password=YOUR_PASSWORD')
  .then(response => response.json())
  .then(data => console.log(data));

// Создать доход
fetch('https://lknpd-nalog.fuckdev.ru', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    method: 'create_income',
    username: 'YOUR_USERNAME',
    password: 'YOUR_PASSWORD',
    amount: 1000,
    comment: 'Услуги'
  })
})
```

## Требования

- PHP 7.4+
- Расширение cURL
- Доступ к https://lknpd.nalog.ru

## Установка

1. Загрузите `index.php` на ваш веб-сервер
2. Убедитесь, что включено расширение cURL
3. Настройте CORS при необходимости

## Примечания

- Все запросы возвращают данные в формате JSON
- Поддерживаются GET и POST запросы
- Для POST можно использовать JSON или form-data
- Временная зона: Europe/Moscow
