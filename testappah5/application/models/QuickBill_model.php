<?php
defined('BASEPATH') or exit('No direct script access allowed');

class QuickBill_model extends CI_Model
{

    public function get_bill_by_id($bill_id) {
        // Use a manual query to fetch bill + customer details
        $sql = "
            SELECT qb.*,
                   c.name AS customer_name,
                   c.mobile AS customer_mobile
            FROM quick_bill qb
            LEFT JOIN customers c ON c.customers_id = qb.customer_id
            WHERE qb.quick_bill_id = ?
            ORDER BY qb.quick_bill_id ASC
        ";

        $query = $this->db->query($sql, [$bill_id]);
        return $query->result();
    }

    public function get_bill_payments($bill_id) {
        $this->db->select('*');
        $this->db->from('quick_bill_payments');
        $this->db->where('quick_bill_id', $bill_id);
        $this->db->order_by('payment_date', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_bill_items($bill_id) {
        $this->db->select('quick_bill_items.*, products.product_name as item_name, products.measurement_unit as unit, products.sku as sku');
        $this->db->from('quick_bill_items');
        $this->db->join('products', 'products.product_id = quick_bill_items.item_id', 'left');
        $this->db->where('quick_bill_id', $bill_id);
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_all_quick_bills($sdate = null, $edate = null) {
        $this->db->select('quick_bill.*, customers.name as customer_name, customers.mobile as customer_mobile, users.UserName as created_by');
        $this->db->from('quick_bill');
        $this->db->join('customers', 'customers.customers_id = quick_bill.customer_id', 'left');
        //join users
        $this->db->join('users', 'users.UserID = quick_bill.created_by', 'left');

        // Apply date filter only if both provided
        if (!empty($sdate) && !empty($edate)) {
            $this->db->where('DATE(quick_bill.created_at) >=', $sdate);
            $this->db->where('DATE(quick_bill.created_at) <=', $edate);
        }

        $this->db->order_by('quick_bill_id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    // Return functionality methods
    public function createReturnRequest($data) {
        $this->db->insert('quick_bill_return_requests', $data);
        return $this->db->insert_id();
    }

    public function createReturnItem($data) {
        $this->db->insert('quick_bill_return_items', $data);
        return $this->db->insert_id();
    }

    public function getReturnApprovals($billId) {
        $this->db->select('qr.*, u.UserName as created_by_name');
        $this->db->from('quick_bill_return_requests qr');
        $this->db->join('users u', 'u.UserID = qr.created_by', 'left');
        $this->db->where('qr.quick_bill_id', $billId);
        $this->db->where('qr.status', 'pending');
        $this->db->order_by('qr.created_at', 'DESC');
        
        $requests = $this->db->get()->result();
        
        // Get items summary for each request
        foreach ($requests as $request) {
            $this->db->select('qri.*, p.product_name');
            $this->db->from('quick_bill_return_items qri');
            $this->db->join('products p', 'p.product_id = qri.item_id', 'left');
            $this->db->where('qri.return_request_id', $request->id);
            
            $items = $this->db->get()->result();
            
            $itemsSummary = [];
            foreach ($items as $item) {
                $itemsSummary[] = $item->product_name . ' (Qty: ' . $item->return_quantity . ')';
            }
            
            $request->items_summary = implode(', ', $itemsSummary);
        }
        
        return $requests;
    }

    public function getAlreadyReturnedQuantity($quickBillItemId) {
        $this->db->select('COALESCE(SUM(qri.return_quantity), 0) as returned_qty');
        $this->db->from('quick_bill_return_items qri');
        $this->db->join('quick_bill_return_requests qrr', 'qrr.id = qri.return_request_id', 'left');
        $this->db->where('qri.quick_bill_item_id', $quickBillItemId);
        $this->db->where('qrr.status', 'approved');
        
        $result = $this->db->get()->row();
        return $result ? (float)$result->returned_qty : 0;
    }

    public function getPendingReturnCount($billId) {
        $this->db->select('COUNT(*) as count');
        $this->db->from('quick_bill_return_requests');
        $this->db->where('quick_bill_id', $billId);
        $this->db->where('status', 'pending');
        
        $result = $this->db->get()->row();
        return $result ? $result->count : 0;
    }

    public function deleteQuickBillWithReason($billId, $deletionReason, $deletedBy) {
        // Start transaction
        $this->db->trans_start();
        
        try {
            // Get bill items before deletion for stock restoration
            $billItems = $this->get_bill_items($billId);
            $itemsSummary = [];
            
            // Return items to stock
            foreach ($billItems as $item) {
                if ($item->po_item_id > 0) {
                    // Update available stock in purchase_order_items
                    $this->db->set('available_stock', 'available_stock + ' . $item->quantity, false);
                    $this->db->where('po_item_id', $item->po_item_id);
                    $this->db->update('purchase_order_items');
                    
                    $itemsSummary[] = $item->item_name . ' (Qty: ' . $item->quantity . ')';
                }
            }
            
            // Store deletion record — fetch bill once to avoid duplicate queries
            $billRecord = $this->get_bill_by_id($billId);
            $billRow = !empty($billRecord) ? $billRecord[0] : null;
            $deletionData = [
                'quick_bill_id'   => $billId,
                'customer_id'     => $billRow->customer_id ?? 0,
                'bill_amount'     => $billRow->final_bill_amount ?? 0,
                'deletion_reason' => $deletionReason,
                'deleted_by'      => $deletedBy,
                'items_summary'   => implode(', ', $itemsSummary),
            ];
            
            $this->db->insert('quick_bill_deletions', $deletionData);
            
            // Delete quick bill items
            $this->db->where('quick_bill_id', $billId);
            $this->db->delete('quick_bill_items');
            
            // Delete quick bill payments
            $this->db->where('quick_bill_id', $billId);
            $this->db->delete('quick_bill_payments');
            
            // Delete from services_job_payments
            $this->db->where('quick_bill_id', $billId);
            $this->db->delete('services_job_payments');
            
            // Delete return requests and items for this bill
            $this->db->where('quick_bill_id', $billId);
            $returnRequests = $this->db->get('quick_bill_return_requests')->result();
            
            foreach ($returnRequests as $request) {
                $this->db->where('return_request_id', $request->id);
                $this->db->delete('quick_bill_return_items');
            }
            
            $this->db->where('quick_bill_id', $billId);
            $this->db->delete('quick_bill_return_requests');
            
            // Delete the main quick bill
            $this->db->where('quick_bill_id', $billId);
            $this->db->delete('quick_bill');
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return ['status' => 'error', 'message' => 'Database transaction failed'];
            }
            
            return ['status' => 'success', 'message' => 'Quick bill deleted successfully'];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['status' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function approveReturn($requestId, $approvedBy) {
        // Start transaction
        $this->db->trans_start();
        
        try {
            // Get return request details
            $request = $this->db->get_where('quick_bill_return_requests', ['id' => $requestId])->row();
            if (!$request) {
                return false;
            }
            
            // Get return items
            $returnItems = $this->db->get_where('quick_bill_return_items', ['return_request_id' => $requestId])->result();
            
            // Update stock quantities and track returned quantities
            foreach ($returnItems as $item) {
                // Get the original quick bill item to find po_item_id
                $qbItem = $this->db->get_where('quick_bill_items', ['id' => $item->quick_bill_item_id])->row();
                if ($qbItem && $qbItem->po_item_id > 0) {
                    // Update available stock in purchase_order_items
                    $this->db->set('available_stock', 'available_stock + ' . $item->return_quantity, false);
                    $this->db->where('po_item_id', $qbItem->po_item_id);
                    $this->db->update('purchase_order_items');
                }
                
                // Update the return tracking fields in quick_bill_items (but DON'T change the original quantity)
                $this->db->where('id', $item->quick_bill_item_id);
                $this->db->update('quick_bill_items', [
                    'return_quantity' => $item->return_quantity,
                    'return_by' => $approvedBy,
                    'return_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Update return request status
            $this->db->where('id', $requestId);
            $this->db->update('quick_bill_return_requests', [
                'status' => 'approved',
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $approvedBy
            ]);
            
            $this->db->trans_complete();
            return $this->db->trans_status() !== FALSE;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return false;
        }
    }

    public function updateQuickBill($billId, $customerId, $paymentMethod, $paidAmount, $cardOrChequeNo, $chequeDate, $bankName, $note, $creditPersonName, $creditPersonPhone, $items, $userId) {
        if (empty($billId) || empty($items)) {
            return ['status' => 'error', 'message' => 'Bill ID and items are required'];
        }

        // Start transaction
        $this->db->trans_start();

        try {
            // Calculate totals
            $billAmount = 0;
            foreach ($items as $item) {
                $billAmount += $item['total'];
            }
            
            error_log("Update Quick Bill - Calculated bill amount: " . $billAmount);

            // Update main bill
            $billData = [
                'customer_id' => $customerId,
                'description' => $note,
                'bill_amount' => $billAmount,
                'final_bill_amount' => $billAmount,
                'total_paid' => $paidAmount,
                'total_balance' => $billAmount - $paidAmount,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId
            ];

            if (strtolower($paymentMethod) === 'credit') {
                $billData['credit_person_name'] = $creditPersonName ?: null;
                $billData['credit_person_phone'] = $creditPersonPhone ?: null;
            } else {
                $billData['credit_person_name'] = null;
                $billData['credit_person_phone'] = null;
            }

            $this->db->where('quick_bill_id', $billId);
            $updateResult = $this->db->update('quick_bill', $billData);
            error_log("Update Quick Bill - Main bill update result: " . ($updateResult ? 'success' : 'failed'));
            if (!$updateResult) {
                error_log("Update Quick Bill - Main bill update failed. Error: " . print_r($this->db->error(), true));
            }

            // Fetch existing items to determine returns (quantity reductions)
            $existingItems = $this->db->get_where('quick_bill_items', ['quick_bill_id' => $billId])->result_array();
            $keyByPo = [];
            foreach ($existingItems as $ex) {
                $keyByPo[$ex['po_item_id']] = $ex;
            }

            // Delete existing items and re-insert from payload
            $this->db->where('quick_bill_id', $billId);
            $deleteResult = $this->db->delete('quick_bill_items');
            error_log("Update Quick Bill - Delete items result: " . ($deleteResult ? 'success' : 'failed'));

            foreach ($items as $item) {
                $newQty = floatval($item['quantity']);
                $poItemId = $item['po_item_id'];
                $oldQty = isset($keyByPo[$poItemId]) ? floatval($keyByPo[$poItemId]['quantity']) : 0;

                // Enforce no increase over original quantity
                if ($newQty > $oldQty) {
                    $this->db->trans_rollback();
                    return ['status' => 'error', 'message' => 'Quantity cannot be increased during edit.'];
                }

                // If quantity decreased, return stock to purchase_order_items.available_stock
                if ($oldQty > $newQty) {
                    $diff = $oldQty - $newQty;
                    $this->db->set('available_stock', 'available_stock + ' . $diff, false);
                    $this->db->where('po_item_id', $poItemId);
                    $this->db->update('purchase_order_items');
                }

                $itemData = [
                    'quick_bill_id' => $billId,
                    'item_id' => $item['product_id'],
                    'po_item_id' => $poItemId,
                    'quantity' => $newQty,
                    'price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'],
                    'discount_amount' => $item['discount_amount'],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $userId
                ];
                $insertResult = $this->db->insert('quick_bill_items', $itemData);
                if (!$insertResult) {
                    error_log("Update Quick Bill - Insert item failed. Error: " . print_r($this->db->error(), true));
                    error_log("Update Quick Bill - Item data: " . print_r($itemData, true));
                }
            }

            // Check if payment record exists, if not create one
            $existingPayment = $this->db->get_where('quick_bill_payments', ['quick_bill_id' => $billId])->row();
            
            if ($existingPayment) {
                // Update existing payment record
                $paymentData = [
                    'paid_amount' => $paidAmount,
                    'payment_method' => $paymentMethod,
                    'card_or_cheque_no' => $cardOrChequeNo ?: null,
                    'cheque_date' => $chequeDate ?: null,
                    'bank_name' => $bankName ?: null,
                    'note' => $note ?: null,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $userId
                ];

                $this->db->where('quick_bill_id', $billId);
                $this->db->update('quick_bill_payments', $paymentData);
            } else {
                // Create new payment record
                $paymentData = [
                    'quick_bill_id' => $billId,
                    'paid_amount' => $paidAmount,
                    'payment_date' => date('Y-m-d H:i:s'),
                    'payment_method' => $paymentMethod,
                    'card_or_cheque_no' => $cardOrChequeNo ?: null,
                    'cheque_date' => $chequeDate ?: null,
                    'bank_name' => $bankName ?: null,
                    'note' => $note ?: null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $userId
                ];

                $this->db->insert('quick_bill_payments', $paymentData);
            }

            // Do not mirror Quick Bill payments into services_job_payments

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $dbError = $this->db->error();
                error_log("Transaction failed in updateQuickBill. Last query: " . $this->db->last_query());
                error_log("Database error: " . print_r($dbError, true));
                return ['status' => 'error', 'message' => 'Failed to update quick bill: ' . (isset($dbError['message']) ? $dbError['message'] : 'Unknown database error')];
            }

            return ['status' => 'success', 'message' => 'Quick bill updated successfully'];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            error_log("Error in updateQuickBill: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'An error occurred while updating the bill: ' . $e->getMessage()];
        }
    }

    public function getBillPaymentHistory($billId) {
        $this->db->select('*');
        $this->db->from('quick_bill_payments');
        $this->db->where('quick_bill_id', $billId);
        $this->db->order_by('payment_date', 'ASC');
        return $this->db->get()->result();
    }

    public function getPaymentById($paymentId) {
        $this->db->select('*');
        $this->db->from('quick_bill_payments');
        $this->db->where('id', $paymentId);
        return $this->db->get()->row();
    }

    public function updatePayment($paymentId, $paymentMethod, $paidAmount, $cardOrChequeNo, $bankName, $chequeDate, $note, $userId) {
        try {
            // Start transaction
            $this->db->trans_start();
            
            // Update payment record
            $paymentData = [
                'payment_method' => $paymentMethod,
                'paid_amount' => $paidAmount,
                'card_or_cheque_no' => $cardOrChequeNo ?: null,
                'bank_name' => $bankName ?: null,
                'cheque_date' => $chequeDate ?: null,
                'note' => $note ?: null,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId
            ];
            
            $this->db->where('id', $paymentId);
            $this->db->update('quick_bill_payments', $paymentData);
            
            // Get the quick_bill_id for this payment
            $payment = $this->db->get_where('quick_bill_payments', ['id' => $paymentId])->row();
            if ($payment) {
                // Update the main bill total_paid
                $this->db->set('total_paid', 'total_paid', false);
                $this->db->where('quick_bill_id', $payment->quick_bill_id);
                $this->db->update('quick_bill');
                
                // Recalculate total_paid from all payments
                $totalPaid = $this->db->select_sum('paid_amount')
                                     ->where('quick_bill_id', $payment->quick_bill_id)
                                     ->get('quick_bill_payments')
                                     ->row()
                                     ->paid_amount;
                
                // Update bill with new total_paid and recalculate balance
                $bill = $this->db->get_where('quick_bill', ['quick_bill_id' => $payment->quick_bill_id])->row();
                if ($bill) {
                    $newBalance = $bill->final_bill_amount - $totalPaid;
                    $this->db->where('quick_bill_id', $payment->quick_bill_id);
                    $this->db->update('quick_bill', [
                        'total_paid' => $totalPaid,
                        'total_balance' => $newBalance,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => $userId
                    ]);
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return ['status' => 'error', 'message' => 'Failed to update payment'];
            }
            
            return ['status' => 'success', 'message' => 'Payment updated successfully'];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            error_log("Error in updatePayment: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'An error occurred while updating the payment: ' . $e->getMessage()];
        }
    }

    public function deletePayment($paymentId, $userId) {
        try {
            // Start transaction
            $this->db->trans_start();
            
            // Get the payment details before deletion
            $payment = $this->db->get_where('quick_bill_payments', ['id' => $paymentId])->row();
            if (!$payment) {
                return ['status' => 'error', 'message' => 'Payment not found'];
            }
            
            // Delete the payment record
            $this->db->where('id', $paymentId);
            $this->db->delete('quick_bill_payments');
            
            // Recalculate total_paid from remaining payments
            $totalPaid = $this->db->select_sum('paid_amount')
                                 ->where('quick_bill_id', $payment->quick_bill_id)
                                 ->get('quick_bill_payments')
                                 ->row()
                                 ->paid_amount;
            
            // Update bill with new total_paid and recalculate balance
            $bill = $this->db->get_where('quick_bill', ['quick_bill_id' => $payment->quick_bill_id])->row();
            if ($bill) {
                $newBalance = $bill->final_bill_amount - ($totalPaid ?: 0);
                $this->db->where('quick_bill_id', $payment->quick_bill_id);
                $this->db->update('quick_bill', [
                    'total_paid' => $totalPaid ?: 0,
                    'total_balance' => $newBalance,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $userId
                ]);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return ['status' => 'error', 'message' => 'Failed to delete payment'];
            }
            
            return ['status' => 'success', 'message' => 'Payment deleted successfully'];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            error_log("Error in deletePayment: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'An error occurred while deleting the payment: ' . $e->getMessage()];
        }
    }

    public function createPayment($billId, $paymentMethod, $paidAmount, $cardOrChequeNo, $bankName, $chequeDate, $note, $userId) {
        try {
            // Start transaction
            $this->db->trans_start();
            
            // Create new payment record
            $paymentData = [
                'quick_bill_id' => $billId,
                'paid_amount' => $paidAmount,
                'payment_date' => date('Y-m-d H:i:s'),
                'payment_method' => $paymentMethod,
                'card_or_cheque_no' => $cardOrChequeNo ?: null,
                'bank_name' => $bankName ?: null,
                'cheque_date' => $chequeDate ?: null,
                'note' => $note ?: null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId
            ];
            
            $this->db->insert('quick_bill_payments', $paymentData);
            
            // Recalculate total_paid from all payments
            $totalPaid = $this->db->select_sum('paid_amount')
                                 ->where('quick_bill_id', $billId)
                                 ->get('quick_bill_payments')
                                 ->row()
                                 ->paid_amount;
            
            // Update bill with new total_paid and recalculate balance
            $bill = $this->db->get_where('quick_bill', ['quick_bill_id' => $billId])->row();
            if ($bill) {
                $newBalance = $bill->final_bill_amount - $totalPaid;
                $this->db->where('quick_bill_id', $billId);
                $this->db->update('quick_bill', [
                    'total_paid' => $totalPaid,
                    'total_balance' => $newBalance,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $userId
                ]);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return ['status' => 'error', 'message' => 'Failed to create payment'];
            }
            
            return ['status' => 'success', 'message' => 'Payment created successfully'];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            error_log("Error in createPayment: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'An error occurred while creating the payment: ' . $e->getMessage()];
        }
    }

}
?>