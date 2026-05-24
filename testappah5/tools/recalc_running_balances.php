<?php
// Run from CLI: php public_html/tools/recalc_running_balances.php [--slug=in_hand] [--update-accounts]

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "This script must be run from the CLI.\n";
    exit(1);
}

// ---- DB CONFIG ----
// If your DB creds change, update these to match application/config/database.php
$DB_HOST = 'localhost';
$DB_USER = 'troja_app';
$DB_PASS = 'StrongLocalPass!';
$DB_NAME = 'trojwfss_service_erp';

// ---- ARG PARSE ----
$args = [
    'slug' => null,
    'updateAccounts' => false,
];
foreach ($argv as $arg) {
    if (strpos($arg, '--slug=') === 0) {
        $args['slug'] = substr($arg, 7);
    }
    if ($arg === '--update-accounts') {
        $args['updateAccounts'] = true;
    }
}

// ---- CONNECT ----
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Failed to connect to MySQL: {$mysqli->connect_error}\n");
    exit(2);
}
$mysqli->set_charset('utf8');

// ---- HELPERS ----
function recalcForSlug(mysqli $db, string $slug, bool $updateAccounts): array {
    $running = 0.0;

    // Fetch transactions oldest -> newest
    $stmt = $db->prepare("SELECT id, txn_type, amount FROM account_transactions WHERE account_slug = ? ORDER BY created_at ASC, id ASC");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_object()) {
        $rows[] = $row;
    }
    $stmt->close();

    $db->begin_transaction();
    try {
        foreach ($rows as $row) {
            $amount = (float)$row->amount;
            if (strtolower($row->txn_type) === 'credit') {
                $running += $amount;
            } else {
                $running -= $amount;
            }

            $up = $db->prepare("UPDATE account_transactions SET running_balance = ? WHERE id = ?");
            $up->bind_param('di', $running, $row->id);
            $up->execute();
            $up->close();
        }

        if ($updateAccounts) {
            $upAcc = $db->prepare("UPDATE accounts SET balance = ? WHERE slug = ?");
            $upAcc->bind_param('ds', $running, $slug);
            $upAcc->execute();
            $upAcc->close();
        }

        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        return [
            'status' => 'error',
            'message' => $e->getMessage(),
        ];
    }

    return [
        'status' => 'success',
        'slug' => $slug,
        'transactions_processed' => count($rows),
        'final_running_balance' => $running,
        'accounts_balance_updated' => $updateAccounts,
    ];
}

// ---- MAIN ----
$slugs = [];
if (!empty($args['slug'])) {
    $slugs = [$args['slug']];
} else {
    $res = $mysqli->query("SELECT slug FROM accounts ORDER BY slug ASC");
    while ($row = $res->fetch_object()) {
        $slugs[] = $row->slug;
    }
}

if (empty($slugs)) {
    echo "No accounts found.\n";
    exit(0);
}

$all = [];
foreach ($slugs as $slug) {
    $all[$slug] = recalcForSlug($mysqli, $slug, $args['updateAccounts']);
}

echo json_encode([
    'status' => 'ok',
    'updated_at' => date('c'),
    'results' => $all,
], JSON_PRETTY_PRINT), "\n";


