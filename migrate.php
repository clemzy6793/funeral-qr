<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

use App\Core\DB;

echo "EventQR Migration Runner\n";
echo "========================\n\n";

$pdo = DB::get();

$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$executed = [];
$stmt = $pdo->query("SELECT migration FROM migrations ORDER BY id");
while ($row = $stmt->fetch()) {
    $executed[] = $row['migration'];
}

$files = glob(__DIR__ . '/migrations/*.sql');
sort($files);

$ran = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $executed)) continue;

    echo "Running: {$name} ... ";
    $sql = file_get_contents($file);

    $lines = preg_replace('/^--.*$/m', '', $sql);
    $statements = array_filter(
        array_map('trim', explode(';', $lines)),
        fn($s) => $s !== ''
    );

    foreach ($statements as $statement) {
        if (trim($statement) === '') continue;
        try {
            $pdo->exec($statement);
        } catch (\PDOException $e) {
            if (!str_contains($e->getMessage(), 'already exists')
                && !str_contains($e->getMessage(), 'duplicate key')) {
                echo "ERROR: {$e->getMessage()}\n";
                exit(1);
            }
        }
    }

    $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)")->execute([$name]);
    echo "DONE\n";
    $ran++;
}

if ($ran === 0) {
    echo "Nothing to migrate.\n";
} else {
    echo "\nRan {$ran} migration(s).\n";
}
