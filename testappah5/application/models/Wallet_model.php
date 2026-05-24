<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wallet_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Payment method to account slug mapping
     */
    private function getAccountSlugForPaymentMethod($paymentMethod)
    {
        $mapping = [
            'cash' => 'in_hand',
            'credit' => 'credits',
            'card' => 'card_machine',
            // Cheque should NOT credit any live wallet. Keep out of bank until cleared.
            'cheque' => null,
            'bank transfer' => 'bank',
            'bank_transfer' => 'bank'  // Support both formats
        ];
        
        return isset($mapping[strtolower($paymentMethod)]) ? $mapping[strtolower($paymentMethod)] : null;
    }

    /**
     * Process payment and update wallet balances
     */
    public function processPayment($paymentData)
    {
        $this->db->trans_start();

        try {
            $methodLower = strtolower($paymentData['payment_method']);
            $accountSlug = $this->getAccountSlugForPaymentMethod($paymentData['payment_method']);

            // Special handling: Cheque payments do NOT touch wallet balances or account_transactions
            if ($methodLower === 'cheque') {
                $this->db->trans_complete();
                return [
                    'status' => 'success',
                    'message' => 'Cheque recorded (no wallet posting)',
                    'account_slug' => null,
                    'transaction_id' => null
                ];
            }

            if (!$accountSlug) {
                throw new Exception('Invalid payment method: ' . $paymentData['payment_method']);
            }

            // Update account balance
            $this->db->set('balance', 'balance + ' . $paymentData['amount'], FALSE);
            $this->db->where('slug', $accountSlug);
            $this->db->update('accounts');

            if ($this->db->affected_rows() == 0) {
                throw new Exception('Account not found: ' . $accountSlug);
            }

            // Fetch updated balance for running balance record
            $account = $this->db->get_where('accounts', ['slug' => $accountSlug])->row();
            $runningBalance = $account ? floatval($account->balance) : null;

            // Create transaction record
            $transactionData = [
                'account_slug' => $accountSlug,
                'description' => $paymentData['description'],
                'amount' => $paymentData['amount'],
                'txn_type' => 'credit',
                'reference_type' => $paymentData['reference_type'],
                'reference_id' => $paymentData['reference_id'],
                'payment_method' => $paymentData['payment_method'],
                'customer_id' => $paymentData['customer_id'] ?? null,
                'created_by' => $paymentData['created_by'],
                'running_balance' => $runningBalance,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('account_transactions', $transactionData);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            return [
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'account_slug' => $accountSlug,
                'transaction_id' => $this->db->insert_id()
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process an outgoing payment (supplier payment, PO payment, etc.)
     * Debits the mapped wallet account and records a 'debit' transaction.
     * Expected $paymentData keys: payment_method, amount, description, reference_type, reference_id, created_by
     */
    public function processOutgoingPayment($paymentData)
    {
        $this->db->trans_start();

        try {
            $methodLower = strtolower($paymentData['payment_method']);
            $accountSlug = $this->getAccountSlugForPaymentMethod($paymentData['payment_method']);

            // Cheque for outgoing should not touch wallet until cleared elsewhere
            if ($methodLower === 'cheque') {
                $this->db->trans_complete();
                return [
                    'status' => 'success',
                    'message' => 'Outgoing cheque recorded (no wallet posting)',
                    'account_slug' => null,
                    'transaction_id' => null
                ];
            }

            if (!$accountSlug) {
                throw new Exception('Invalid payment method: ' . $paymentData['payment_method']);
            }

            // Debit the account balance
            $this->db->set('balance', 'balance - ' . $paymentData['amount'], FALSE);
            $this->db->where('slug', $accountSlug);
            $this->db->update('accounts');

            if ($this->db->affected_rows() == 0) {
                throw new Exception('Account not found: ' . $accountSlug);
            }

            // Fetch updated balance for running balance record
            $account = $this->db->get_where('accounts', ['slug' => $accountSlug])->row();
            $runningBalance = $account ? floatval($account->balance) : null;

            // Create transaction record
            $transactionData = [
                'account_slug' => $accountSlug,
                'description' => $paymentData['description'],
                'amount' => $paymentData['amount'],
                'txn_type' => 'debit',
                'reference_type' => $paymentData['reference_type'],
                'reference_id' => $paymentData['reference_id'],
                'payment_method' => $paymentData['payment_method'],
                'customer_id' => $paymentData['customer_id'] ?? null,
                'created_by' => $paymentData['created_by'],
                'running_balance' => $runningBalance,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('account_transactions', $transactionData);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            return [
                'status' => 'success',
                'message' => 'Outgoing payment processed successfully',
                'account_slug' => $accountSlug,
                'transaction_id' => $this->db->insert_id()
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process multiple payments for a bill
     */
    public function processMultiplePayments($payments, $referenceType, $referenceId, $customerId = null, $createdBy = null)
    {
        $results = [];
        $totalProcessed = 0;

        foreach ($payments as $payment) {
            $paymentData = [
                'payment_method' => $payment['method'],
                'amount' => floatval($payment['amount']),
                'description' => $this->generatePaymentDescription($payment, $referenceType, $referenceId),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'customer_id' => $customerId,
                'created_by' => $createdBy
            ];

            $result = $this->processPayment($paymentData);
            $results[] = $result;

            if ($result['status'] === 'success') {
                $totalProcessed += $paymentData['amount'];
            }
        }

        return [
            'status' => 'success',
            'total_processed' => $totalProcessed,
            'results' => $results
        ];
    }

    /**
     * Generate payment description
     */
    private function generatePaymentDescription($payment, $referenceType, $referenceId)
    {
        $method = ucfirst($payment['method']);
        $amount = number_format($payment['amount'], 2);
        
        $description = "{$method} payment of LKR {$amount}";
        
        if ($referenceType === 'quick_bill') {
            $description .= " for Quick Bill #{$referenceId}";
        } elseif ($referenceType === 'service_job') {
            $description .= " for Service Job #{$referenceId}";
        }

        // Add additional details if available
        if (!empty($payment['cardOrChequeNo'])) {
            $description .= " (Ref: {$payment['cardOrChequeNo']})";
        }
        
        if (!empty($payment['bankName'])) {
            $description .= " - {$payment['bankName']}";
        }

        return $description;
    }

    /**
     * Get account balance by slug
     */
    public function getAccountBalance($slug)
    {
        $account = $this->db->get_where('accounts', ['slug' => $slug])->row();
        return $account ? $account->balance : 0;
    }

    /**
     * Get all wallet accounts
     */
    public function getAllAccounts()
    {
        return $this->db->get('accounts')->result();
    }

    /**
     * Get account transactions
     */
    public function getAccountTransactions($slug, $limit = 20)
    {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get_where('account_transactions', ['account_slug' => $slug])->result();
    }

    /**
     * Add loan transaction into the single loan wallet with directional flag
     * $direction: 'in' or 'out'
     * For 'in' we treat as credit to loan account; for 'out' as debit from loan account.
     */
    public function addLoanTransaction($direction, $amount, $description, $referenceType = 'loan', $referenceId = null, $createdBy = null, $companyName = null, $expectedReturnDate = null)
    {
        $direction = strtolower($direction) === 'out' ? 'out' : 'in';
        $amount = floatval($amount);

        $this->db->trans_start();
        try {
            // Ensure loan account exists
            $account = $this->db->get_where('accounts', ['slug' => 'loan'])->row();
            if (!$account) {
                $this->db->insert('accounts', [
                    'slug' => 'loan',
                    'name' => 'Loan',
                    'balance' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $account = $this->db->get_where('accounts', ['slug' => 'loan'])->row();
            }

            // Update balance: in => credit, out => debit
            if ($direction === 'in') {
                $this->db->set('balance', 'balance + ' . $amount, FALSE);
            } else {
                $this->db->set('balance', 'balance - ' . $amount, FALSE);
            }
            $this->db->where('slug', 'loan');
            $this->db->update('accounts');

            // Fetch updated balance
            $account = $this->db->get_where('accounts', ['slug' => 'loan'])->row();
            $runningBalance = $account ? floatval($account->balance) : null;

            // Insert transaction
            $txnType = $direction === 'in' ? 'credit' : 'debit';
            $txn = [
                'account_slug' => 'loan',
                'description' => $description,
                'amount' => $amount,
                'running_balance' => $runningBalance,
                'txn_type' => $txnType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'payment_method' => 'loan',
                'loan_direction' => $direction,
                'company_name' => $companyName,
                'expected_return_date' => $expectedReturnDate,
                'customer_id' => null,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('account_transactions', $txn);

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            return [ 'status' => 'success', 'transaction_id' => $this->db->insert_id(), 'running_balance' => $runningBalance ];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [ 'status' => 'error', 'message' => $e->getMessage() ];
        }
    }

    /**
     * Record a return payment against a previous OUT loan transaction
     * It credits the loan account and increments returned_total on the parent.
     */
    public function returnLoan($parentTxnId, $amount, $description, $createdBy = null)
    {
        $amount = floatval($amount);
        $parent = $this->db->get_where('account_transactions', ['txn_id' => $parentTxnId, 'account_slug' => 'loan'])->row();
        if (!$parent) {
            return ['status' => 'error', 'message' => 'Parent loan not found'];
        }

        $this->db->trans_start();
        try {
            // Credit loan account
            $this->db->set('balance', 'balance + ' . $amount, FALSE);
            $this->db->where('slug', 'loan');
            $this->db->update('accounts');

            // Updated balance
            $account = $this->db->get_where('accounts', ['slug' => 'loan'])->row();
            $runningBalance = $account ? floatval($account->balance) : null;

            // Insert return transaction referencing parent
            $txn = [
                'account_slug' => 'loan',
                'description' => $description,
                'amount' => $amount,
                'running_balance' => $runningBalance,
                'txn_type' => 'credit',
                'reference_type' => 'loan_return',
                'reference_id' => null,
                'payment_method' => 'loan',
                'loan_direction' => 'in',
                'parent_txn_id' => $parentTxnId,
                'customer_id' => null,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('account_transactions', $txn);

            // Update returned_total on parent
            $this->db->set('returned_total', 'returned_total + ' . $amount, FALSE);
            $this->db->where('txn_id', $parentTxnId);
            $this->db->update('account_transactions');

            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Return transaction failed');
            }
            return ['status' => 'success', 'transaction_id' => $this->db->insert_id(), 'running_balance' => $runningBalance];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Process payment reversal (debit from account)
     */
    public function processPaymentReversal($paymentData)
    {
        $this->db->trans_start();

        try {
            $accountSlug = $this->getAccountSlugForPaymentMethod($paymentData['payment_method']);
            
            if (!$accountSlug) {
                throw new Exception('Invalid payment method: ' . $paymentData['payment_method']);
            }

            // Update account balance (debit - reverse the original credit)
            $this->db->set('balance', 'balance - ' . $paymentData['amount'], FALSE);
            $this->db->where('slug', $accountSlug);
            $this->db->update('accounts');

            if ($this->db->affected_rows() == 0) {
                throw new Exception('Account not found: ' . $accountSlug);
            }

            // Fetch updated balance for running balance record
            $account = $this->db->get_where('accounts', ['slug' => $accountSlug])->row();
            $runningBalance = $account ? floatval($account->balance) : null;

            // Create reversal transaction record
            $transactionData = [
                'account_slug' => $accountSlug,
                'description' => $paymentData['description'],
                'amount' => $paymentData['amount'],
                'txn_type' => 'debit',
                'reference_type' => $paymentData['reference_type'],
                'reference_id' => $paymentData['reference_id'],
                'payment_method' => $paymentData['payment_method'],
                'customer_id' => $paymentData['customer_id'] ?? null,
                'created_by' => $paymentData['created_by'],
                'running_balance' => $runningBalance,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('account_transactions', $transactionData);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            return [
                'status' => 'success',
                'message' => 'Payment reversal processed successfully',
                'account_slug' => $accountSlug,
                'transaction_id' => $this->db->insert_id()
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Transfer money between accounts
     */
    public function transferBetweenAccounts($fromSlug, $toSlug, $amount, $reason, $note = null, $createdBy = null)
    {
        $amount = floatval($amount);
        
        if ($amount <= 0) {
            return ['status' => 'error', 'message' => 'Invalid transfer amount'];
        }
        
        if ($fromSlug === $toSlug) {
            return ['status' => 'error', 'message' => 'Cannot transfer to the same account'];
        }
        
        $this->db->trans_start();
        
        try {
            // Check if both accounts exist
            $fromAccount = $this->db->get_where('accounts', ['slug' => $fromSlug])->row();
            $toAccount = $this->db->get_where('accounts', ['slug' => $toSlug])->row();
            
            if (!$fromAccount || !$toAccount) {
                throw new Exception('One or both accounts not found');
            }
            
            // Check if source account has sufficient balance
            if ($fromAccount->balance < $amount) {
                throw new Exception('Insufficient balance in source account');
            }
            
            // Debit from source account
            $this->db->set('balance', 'balance - ' . $amount, FALSE);
            $this->db->where('slug', $fromSlug);
            $this->db->update('accounts');
            
            // Credit to target account
            $this->db->set('balance', 'balance + ' . $amount, FALSE);
            $this->db->where('slug', $toSlug);
            $this->db->update('accounts');
            
            // Get updated balances for running balance records
            $fromAccountUpdated = $this->db->get_where('accounts', ['slug' => $fromSlug])->row();
            $toAccountUpdated = $this->db->get_where('accounts', ['slug' => $toSlug])->row();
            
            // Create debit transaction for source account
            $debitTxn = [
                'account_slug' => $fromSlug,
                'description' => 'Transfer to ' . $toAccount->name . ($reason ? ' - ' . $reason : ''),
                'amount' => $amount,
                'running_balance' => $fromAccountUpdated ? floatval($fromAccountUpdated->balance) : null,
                'txn_type' => 'debit',
                'reference_type' => 'transfer',
                'reference_id' => null,
                'payment_method' => 'transfer',
                'customer_id' => null,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('account_transactions', $debitTxn);
            $debitTxnId = $this->db->insert_id();
            
            // Create credit transaction for target account
            $creditTxn = [
                'account_slug' => $toSlug,
                'description' => 'Transfer from ' . $fromAccount->name . ($reason ? ' - ' . $reason : ''),
                'amount' => $amount,
                'running_balance' => $toAccountUpdated ? floatval($toAccountUpdated->balance) : null,
                'txn_type' => 'credit',
                'reference_type' => 'transfer',
                'reference_id' => $debitTxnId, // Link to the debit transaction
                'payment_method' => 'transfer',
                'customer_id' => null,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('account_transactions', $creditTxn);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transfer transaction failed');
            }
            
            return [
                'status' => 'success',
                'message' => 'Transfer completed successfully',
                'debit_txn_id' => $debitTxnId,
                'credit_txn_id' => $this->db->insert_id(),
                'from_balance' => $fromAccountUpdated ? floatval($fromAccountUpdated->balance) : 0,
                'to_balance' => $toAccountUpdated ? floatval($toAccountUpdated->balance) : 0
            ];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Process expense (debit from account)
     */
    public function processExpense($expenseData)
    {
        $this->db->trans_start();

        try {
            $accountSlug = $expenseData['account_slug'];
            $amount = $expenseData['amount'];

            // Update account balance (debit)
            $this->db->set('balance', 'balance - ' . $amount, FALSE);
            $this->db->where('slug', $accountSlug);
            $this->db->update('accounts');

            if ($this->db->affected_rows() == 0) {
                throw new Exception('Account not found: ' . $accountSlug);
            }

            // Fetch updated balance for running balance record
            $account = $this->db->get_where('accounts', ['slug' => $accountSlug])->row();
            $runningBalance = $account ? floatval($account->balance) : null;

            // Create transaction record
            $transactionData = [
                'account_slug' => $accountSlug,
                'description' => $expenseData['description'],
                'amount' => $amount,
                'txn_type' => 'debit',
                'reference_type' => $expenseData['reference_type'],
                'reference_id' => $expenseData['reference_id'],
                'payment_method' => 'expense',
                'customer_id' => null,
                'created_by' => $expenseData['created_by'],
                'running_balance' => $runningBalance,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('account_transactions', $transactionData);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            return [
                'status' => 'success',
                'message' => 'Expense processed successfully',
                'account_slug' => $accountSlug,
                'transaction_id' => $this->db->insert_id()
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Recalculate running balances for an account based on transaction history
     */
    public function recalculateRunningBalancesForAccount($accountSlug)
    {
        // Start from zero and apply transactions in chronological order
        $this->db->order_by('created_at', 'ASC');
        $this->db->order_by('id', 'ASC');
        $transactions = $this->db->get_where('account_transactions', ['account_slug' => $accountSlug])->result();

        $running = 0.0;
        foreach ($transactions as $t) {
            if ($t->txn_type === 'credit') {
                $running += floatval($t->amount);
            } else { // debit
                $running -= floatval($t->amount);
            }
            $this->db->where('id', $t->id);
            $this->db->update('account_transactions', ['running_balance' => $running]);
        }

        return [
            'status' => 'success',
            'message' => 'Running balances recalculated for ' . $accountSlug,
            'final_balance' => $running
        ];
    }

    /**
     * Recalculate running balances for all known accounts
     */
    public function recalculateRunningBalancesForAll()
    {
        $accounts = $this->getAllAccounts();
        $results = [];
        foreach ($accounts as $acc) {
            $results[$acc->slug] = $this->recalculateRunningBalancesForAccount($acc->slug);
        }
        return $results;
    }
}
