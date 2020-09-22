<?php

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'renderer' => [
            'template_path' => DIR . 'app' . DS . 'views',
            'cache_path' => false,
        ],
        'chasha_app_settings' => [
            'db' => [
                'dsn' => 'mysql:host=127.0.0.1;dbname=portal;charset=utf8mb4',
                'dbname' => 'portal',
                'username' => 'root',
                'password' => '',
                'options' => [
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                ],
            ],
            'cyrillic' => false,
            'pagination' => [
                'per_page' => 10,
                'page_span' => 4,
            ],
            'mail' => [
                'host' => 'mail.kg.org.rs',
                'username' => 'portal@kg.org.rs',
                'password' => 'portal2020',
                'port' => 465, // 465 = ssl, 587 = tls
                'from' => 'portal@kg.org.rs',
                'from_name' => 'GU KG portal',
            ],
        ]
    ],
];
