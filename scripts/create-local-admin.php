<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("Not found.\n");
}

require_once dirname(__DIR__) . '/database/runner.php';

try {
    $options = ['database' => config_value('DB_NAME', 'guidemypc') ?? 'guidemypc', 'email' => '', 'name' => ''];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help') {
            exit("Usage: php scripts/create-local-admin.php --name=\"Local Admin\" --email=admin@example.test [--database=guidemypc]\n");
        }

        foreach (['database', 'email', 'name'] as $key) {
            $prefix = '--' . $key . '=';

            if (str_starts_with($argument, $prefix)) {
                $options[$key] = substr($argument, strlen($prefix));
                continue 2;
            }
        }

        throw new InvalidArgumentException(sprintf('Unknown option: %s', $argument));
    }

    if (preg_match('/^[A-Za-z0-9_]+$/', $options['database']) !== 1) {
        throw new InvalidArgumentException('Database names may contain only letters, numbers, and underscores.');
    }

    if (trim($options['name']) === '' || mb_strlen($options['name']) > 100) {
        throw new InvalidArgumentException('Provide a name between 1 and 100 characters.');
    }

    if (filter_var($options['email'], FILTER_VALIDATE_EMAIL) === false || mb_strlen($options['email']) > 150) {
        throw new InvalidArgumentException('Provide a valid email address up to 150 characters.');
    }

    fwrite(STDOUT, "Enter a new password (input is visible in this terminal): ");
    $password = trim((string) fgets(STDIN));
    fwrite(STDOUT, "Confirm the password: ");
    $confirmation = trim((string) fgets(STDIN));

    if (mb_strlen($password) < 12) {
        throw new InvalidArgumentException('The password must be at least 12 characters.');
    }

    if (!hash_equals($password, $confirmation)) {
        throw new InvalidArgumentException('The passwords do not match.');
    }

    $connection = database_connection($options['database'], false);

    try {
        $migrationTable = $connection->query("SHOW TABLES LIKE 'schema_migrations'");

        if ($migrationTable->num_rows === 0) {
            throw new RuntimeException('Run database/migrate.php before creating an administrator.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        $statement = $connection->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
        $statement->bind_param('ssss', $options['name'], $options['email'], $hash, $role);
        $statement->execute();
        $statement->close();
        fwrite(STDOUT, "Local administrator created.\n");
    } finally {
        $connection->close();
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Admin creation failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
