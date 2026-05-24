<?php
// Run from CLI: php public_html/tools/setup_loans.php
if (php_sapi_name() !== 'cli') { echo "CLI only\n"; exit(1); }

$DB_HOST = 'localhost';
$DB_USER = 'troja_app';
$DB_PASS = 'StrongLocalPass!';
$DB_NAME = 'trojwfss_service_erp';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) { fwrite(STDERR, $mysqli->connect_error."\n"); exit(2);} 
$mysqli->set_charset('utf8');

// 1) Ensure loan account exists
$res = $mysqli->query("SELECT id FROM accounts WHERE slug='loan' LIMIT 1");
if ($res && $res->num_rows === 0) {
  $mysqli->query("INSERT INTO accounts (slug,name,balance,created_at,updated_at) VALUES ('loan','Loan',0,NOW(),NOW())");
  echo "Created loan account.\n";
} else {
  echo "Loan account exists.\n";
}

// 2) Ensure running_balance column exists in account_transactions
$res2 = $mysqli->query("SHOW COLUMNS FROM account_transactions LIKE 'running_balance'");
if ($res2 && $res2->num_rows === 0) {
  $mysqli->query("ALTER TABLE account_transactions ADD COLUMN running_balance DECIMAL(15,2) NULL AFTER amount");
  echo "Added running_balance column.\n";
} else {
  echo "running_balance column exists.\n";
}

// 3) Ensure loan_direction column exists in account_transactions
$res3 = $mysqli->query("SHOW COLUMNS FROM account_transactions LIKE 'loan_direction'");
if ($res3 && $res3->num_rows === 0) {
  $mysqli->query("ALTER TABLE account_transactions ADD COLUMN loan_direction ENUM('in','out') NULL AFTER payment_method");
  echo "Added loan_direction column.\n";
} else {
  echo "loan_direction column exists.\n";
}

echo "Done.\n";


