<?php

declare(strict_types=1);

function application_database_connection(): mysqli
{
    return GuideMyPC\Core\Database::connect([
        'host' => config_value('DB_HOST'),
        'user' => config_value('DB_USER'),
        'password' => config_value('DB_PASSWORD', ''),
        'database' => config_value('DB_NAME'),
        'port' => config_value('DB_PORT', '3306'),
    ]);
}
