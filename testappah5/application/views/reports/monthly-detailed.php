<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <h4>Detailed Monthly Report - <?= date('F', mktime(0,0,0,$report_month,1)) ?> <?= $report_year ?></h4>
        <div class="alert alert-info mt-2" style="font-size: 0.95rem;">
          <strong>About this report</strong>
          <ul class="mb-0">
            <li><strong>Total Month Income</strong>: Sum of received payments by method across Quick Bills and Service Jobs: Cash + Card + Bank Transfer + Credit. Cheques are listed but not counted until received into bank as per your cheque flow.</li>
            <li><strong>Cash/Card/Bank Transfer/Credit</strong>: Aggregated from <code>quick_bill_payments</code> and <code>services_job_payments</code> within the month. Service rows linked to Quick Bills are excluded from the service aggregation to avoid double count.</li>
            <li><strong>Total Expenses</strong>: In-house expenses (from <code>expenses</code> table) + Used Item Expenses (company cost of items consumed on Service Jobs), for the selected month.</li>
            <li><strong>In House Expenses</strong>: Sum of expenses by payment method (Cash/Card/Bank Transfer/Cheque) between month start and end.</li>
            <li><strong>Used Item Expenses</strong>: Cost of products consumed on Service Jobs: 
              <em>COALESCE(po_item.company_price, last known company price by product) × quantity</em> summed in month.</li>
            <li><strong>Profit</strong>: Total Month Income − (In House Expenses + Used Item Expenses).</li>
            <li><strong>Bank Deposit (In Hand → Bank)</strong>: Sum of wallet transfer credits into <code>bank</code> where the corresponding debit is from <code>in_hand</code>.</li>
            <li><strong>Total Bank Credits</strong>: All credit entries to <code>bank</code> in the month (transfers, cheque clearings, etc.).</li>
            <li><strong>Cheque Payments</strong>: Cheque amounts paid in the month (not auto-added to bank balance until you mark as received).</li>
            <li><strong>Return to In Hand</strong>: Wallet transfer credits into <code>in_hand</code> where the matching debit is from <code>bank</code> or <code>card_machine</code>.</li>
            <li><strong>Supplier Invoice/Payment/Balance</strong>: Invoices from <code>purchase_orders</code> by bill date; payments from <code>purchase_order_payments</code>; balance = invoice − payments.</li>
            <li><strong>Company Loan In/Out</strong>: From <code>account_transactions</code> where <code>account_slug='loan'</code> credits (in) and debits (out) in the month.</li>
            <li><strong>Mechanic Item Buy/Sell/Profit</strong>: From <code>outsource_parts</code> joined to jobs in the month; profit = sell − buy.</li>
            <li><strong>Mechanic Labour</strong>: Sum of confirmed labour lines (<code>item_type='labour'</code>) on Service Jobs in the month.</li>
            <li><strong>Wallet Transactions</strong>: All account transactions in the month grouped by In Hand / Bank / Card Machine, with running balances as stored.</li>
            <li><strong>Profit Breakdown (COGS)</strong>: For each Quick Bill and Service Job in the month, shows bill amount and cost-of-goods (COGS) derived from purchase prices.</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><strong>Quick Bills</strong></div>
          <div class="card-body">
            <table class="table table-sm table-striped">
              <thead class="table-light">
                <tr><th>ID</th><th>Customer</th><th>Description</th><th class="text-end">Amount</th><th>Date</th></tr>
              </thead>
              <tbody>
                <?php foreach($quick_bills as $qb): ?>
                  <tr>
                    <td>QB-<?= $qb->quick_bill_id ?></td>
                    <td><?= htmlspecialchars($qb->customer_name ?: 'Walk-in') ?></td>
                    <td><small><?= htmlspecialchars($qb->description ?: '-') ?></small></td>
                    <td class="text-end">Rs. <?= number_format($qb->final_bill_amount,2) ?></td>
                    <td><?= date('Y-m-d', strtotime($qb->created_at)) ?></td>
                  </tr>
                <?php endforeach; if(empty($quick_bills)): ?>
                  <tr><td colspan="5" class="text-muted">No records</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><strong>Service Jobs - Normal</strong></div>
          <div class="card-body">
            <table class="table table-sm table-striped">
              <thead class="table-light">
                <tr><th>ID</th><th>Customer</th><th>Vehicle</th><th class="text-end">Amount</th><th>Date</th></tr>
              </thead>
              <tbody>
                <?php foreach($normal_services as $sj): ?>
                  <tr>
                    <td>JOB-<?= $sj->job_id ?></td>
                    <td><?= htmlspecialchars($sj->customer_name ?: '-') ?></td>
                    <td><?= htmlspecialchars($sj->vehicle_no ?: '-') ?></td>
                    <td class="text-end">Rs. <?= number_format($sj->final_bill_amount,2) ?></td>
                    <td><?= date('Y-m-d', strtotime($sj->service_date)) ?></td>
                  </tr>
                <?php endforeach; if(empty($normal_services)): ?>
                  <tr><td colspan="5" class="text-muted">No records</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><strong>Service Jobs - Mechanical</strong></div>
          <div class="card-body">
            <table class="table table-sm table-striped">
              <thead class="table-light">
                <tr><th>ID</th><th>Customer</th><th>Vehicle</th><th class="text-end">Amount</th><th>Date</th></tr>
              </thead>
              <tbody>
                <?php foreach($mechanical_services as $sj): ?>
                  <tr>
                    <td>JOB-<?= $sj->job_id ?></td>
                    <td><?= htmlspecialchars($sj->customer_name ?: '-') ?></td>
                    <td><?= htmlspecialchars($sj->vehicle_no ?: '-') ?></td>
                    <td class="text-end">Rs. <?= number_format($sj->final_bill_amount,2) ?></td>
                    <td><?= date('Y-m-d', strtotime($sj->service_date)) ?></td>
                  </tr>
                <?php endforeach; if(empty($mechanical_services)): ?>
                  <tr><td colspan="5" class="text-muted">No records</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><strong>Payments</strong></div>
          <div class="card-body">
            <h6>Quick Bill Payments</h6>
            <table class="table table-sm">
              <thead class="table-light"><tr><th>Date</th><th>Method</th><th class="text-end">Amount</th><th>QB</th><th>Customer</th></tr></thead>
              <tbody>
                <?php foreach($payment_details['quick_bill_payments'] as $p): ?>
                  <tr>
                    <td><?= date('Y-m-d', strtotime($p->payment_date)) ?></td>
                    <td><?= htmlspecialchars($p->payment_method) ?></td>
                    <td class="text-end">Rs. <?= number_format($p->paid_amount,2) ?></td>
                    <td>QB-<?= $p->quick_bill_id ?></td>
                    <td><?= htmlspecialchars($p->customer_name ?: '-') ?></td>
                  </tr>
                <?php endforeach; if(empty($payment_details['quick_bill_payments'])): ?>
                  <tr><td colspan="5" class="text-muted">No records</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
            <h6 class="mt-3">Service Payments</h6>
            <table class="table table-sm">
              <thead class="table-light"><tr><th>Date</th><th>Method</th><th class="text-end">Amount</th><th>JOB</th><th>Customer</th></tr></thead>
              <tbody>
                <?php foreach($payment_details['service_payments'] as $p): ?>
                  <tr>
                    <td><?= date('Y-m-d', strtotime($p->payment_date)) ?></td>
                    <td><?= htmlspecialchars($p->payment_method) ?></td>
                    <td class="text-end">Rs. <?= number_format($p->paid_amount,2) ?></td>
                    <td>JOB-<?= $p->job_id ?></td>
                    <td><?= htmlspecialchars($p->customer_name ?: '-') ?></td>
                  </tr>
                <?php endforeach; if(empty($payment_details['service_payments'])): ?>
                  <tr><td colspan="5" class="text-muted">No records</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><strong>Expenses</strong></div>
          <div class="card-body">
            <table class="table table-sm table-striped">
              <thead class="table-light"><tr><th>Date</th><th>Category</th><th>Description</th><th class="text-end">Amount</th><th>Method</th></tr></thead>
              <tbody>
                <?php foreach($expense_details as $e): ?>
                  <tr>
                    <td><?= date('Y-m-d', strtotime($e->expense_date)) ?></td>
                    <td><?= htmlspecialchars($e->category_name ?: '-') ?></td>
                    <td><small><?= htmlspecialchars($e->description ?: '-') ?></small></td>
                    <td class="text-end">Rs. <?= number_format($e->amount,2) ?></td>
                    <td><?= htmlspecialchars($e->payment_method) ?></td>
                  </tr>
                <?php endforeach; if(empty($expense_details)): ?>
                  <tr><td colspan="5" class="text-muted">No records</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><strong>Wallet Transactions</strong></div>
          <div class="card-body">
            <?php foreach(['in_hand'=>'In Hand','bank'=>'Bank','card_machine'=>'Card Machine'] as $slug=>$label): ?>
              <h6><?= $label ?></h6>
              <table class="table table-sm">
                <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Description</th><th class="text-end">Amount</th><th class="text-end">Running</th></tr></thead>
                <tbody>
                  <?php foreach(($balance_transactions[$slug] ?? []) as $t): ?>
                    <tr>
                      <td><?= date('Y-m-d', strtotime($t->created_at)) ?></td>
                      <td><span class="badge <?= $t->txn_type=='credit'?'bg-success':'bg-danger' ?>"><?= strtoupper($t->txn_type) ?></span></td>
                      <td><small><?= htmlspecialchars($t->description) ?></small></td>
                      <td class="text-end">Rs. <?= number_format($t->amount,2) ?></td>
                      <td class="text-end">Rs. <?= number_format($t->running_balance,2) ?></td>
                    </tr>
                  <?php endforeach; if(empty($balance_transactions[$slug])): ?>
                    <tr><td colspan="5" class="text-muted">No records</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-12">
        <div class="card">
          <div class="card-header"><strong>Profit Breakdown (COGS)</strong></div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <h6>Quick Bills</h6>
                <table class="table table-sm">
                  <thead class="table-light"><tr><th>QB</th><th>Customer</th><th class="text-end">Bill</th><th class="text-end">COGS</th></tr></thead>
                  <tbody>
                    <?php foreach($profit_details['quick_bill_profits'] as $r): ?>
                      <tr>
                        <td>QB-<?= $r->quick_bill_id ?></td>
                        <td><?= htmlspecialchars($r->customer_name ?: '-') ?></td>
                        <td class="text-end">Rs. <?= number_format($r->final_bill_amount,2) ?></td>
                        <td class="text-end">Rs. <?= number_format($r->cogs,2) ?></td>
                      </tr>
                    <?php endforeach; if(empty($profit_details['quick_bill_profits'])): ?>
                      <tr><td colspan="4" class="text-muted">No records</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                <h6>Service Jobs</h6>
                <table class="table table-sm">
                  <thead class="table-light"><tr><th>JOB</th><th>Customer</th><th class="text-end">Bill</th><th class="text-end">COGS</th></tr></thead>
                  <tbody>
                    <?php foreach($profit_details['service_profits'] as $r): ?>
                      <tr>
                        <td>JOB-<?= $r->job_id ?></td>
                        <td><?= htmlspecialchars($r->customer_name ?: '-') ?></td>
                        <td class="text-end">Rs. <?= number_format($r->final_bill_amount,2) ?></td>
                        <td class="text-end">Rs. <?= number_format($r->cogs,2) ?></td>
                      </tr>
                    <?php endforeach; if(empty($profit_details['service_profits'])): ?>
                      <tr><td colspan="4" class="text-muted">No records</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>


