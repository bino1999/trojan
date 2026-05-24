<div class="page-content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Detailed Daily Report - <?= date('F j, Y', strtotime($report_date)) ?></h4>
					<div>
						<a href="<?= base_url('reports/daily') ?>" class="btn btn-secondary">
							<i class="fas fa-arrow-left"></i> Back to Summary
						</a>
						<button onclick="window.print()" class="btn btn-primary">
							<i class="fas fa-print"></i> Print
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Quick Bills Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-primary text-white">
						<h5 class="mb-0">
							<i class="fas fa-receipt"></i> Quick Bill Transactions
							<span class="badge bg-light text-dark ms-2"><?= count($quick_bills) ?> transactions</span>
						</h5>
					</div>
					<div class="card-body">
						<?php if (!empty($quick_bills)): ?>
							<div class="table-responsive">
								<table class="table table-striped table-hover">
									<thead class="table-dark">
										<tr>
											<th>Bill ID</th>
											<th>Customer</th>
											<th>Contact</th>
											<th>Description</th>
											<th>Amount</th>
											<th>Time</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($quick_bills as $bill): ?>
											<tr>
												<td><strong>#<?= $bill->quick_bill_id ?></strong></td>
												<td><?= htmlspecialchars($bill->customer_name ?: 'Walk-in Customer') ?></td>
												<td><?= htmlspecialchars($bill->customer_mobile ?: '-') ?></td>
												<td><?= htmlspecialchars($bill->description ?: 'Quick Bill') ?></td>
												<td class="text-end"><strong>Rs. <?= number_format($bill->final_bill_amount, 2) ?></strong></td>
												<td><?= date('H:i', strtotime($bill->created_at)) ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
									<tfoot class="table-info">
										<tr>
											<th colspan="4" class="text-end">Total Quick Bill Income:</th>
											<th class="text-end">Rs. <?= number_format(array_sum(array_column($quick_bills ?: [], 'final_bill_amount')), 2) ?></th>
											<th></th>
										</tr>
									</tfoot>
								</table>
							</div>
						<?php else: ?>
							<div class="text-center py-4">
								<i class="fas fa-receipt fa-3x text-muted mb-3"></i>
								<p class="text-muted">No Quick Bill transactions found for this date.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Normal Services Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-success text-white">
						<h5 class="mb-0">
							<i class="fas fa-tools"></i> Normal Service Jobs
							<span class="badge bg-light text-dark ms-2"><?= count($normal_services) ?> jobs</span>
						</h5>
					</div>
					<div class="card-body">
						<?php if (!empty($normal_services)): ?>
							<div class="table-responsive">
								<table class="table table-striped table-hover">
									<thead class="table-dark">
										<tr>
											<th>Job ID</th>
											<th>Customer</th>
											<th>Vehicle</th>
											<th>Description</th>
											<th>Amount</th>
											<th>Service Date</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($normal_services as $service): ?>
											<tr>
												<td><strong>#<?= $service->job_id ?></strong></td>
												<td><?= htmlspecialchars($service->customer_name) ?></td>
												<td><?= htmlspecialchars($service->vehicle_no ?: 'N/A') ?></td>
												<td><?= htmlspecialchars($service->description ?: 'Normal Service') ?></td>
												<td class="text-end"><strong>Rs. <?= number_format($service->final_bill_amount, 2) ?></strong></td>
												<td><?= date('H:i', strtotime($service->service_date)) ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
									<tfoot class="table-info">
										<tr>
											<th colspan="4" class="text-end">Total Normal Service Income:</th>
											<th class="text-end">Rs. <?= number_format(array_sum(array_column($normal_services ?: [], 'final_bill_amount')), 2) ?></th>
											<th></th>
										</tr>
									</tfoot>
								</table>
							</div>
						<?php else: ?>
							<div class="text-center py-4">
								<i class="fas fa-tools fa-3x text-muted mb-3"></i>
								<p class="text-muted">No Normal Service jobs found for this date.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Mechanical Services Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-warning text-dark">
						<h5 class="mb-0">
							<i class="fas fa-cog"></i> Mechanical Service Jobs
							<span class="badge bg-light text-dark ms-2"><?= count($mechanical_services) ?> jobs</span>
						</h5>
					</div>
					<div class="card-body">
						<?php if (!empty($mechanical_services)): ?>
							<div class="table-responsive">
								<table class="table table-striped table-hover">
									<thead class="table-dark">
										<tr>
											<th>Job ID</th>
											<th>Customer</th>
											<th>Vehicle</th>
											<th>Description</th>
											<th>Amount</th>
											<th>Service Date</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($mechanical_services as $service): ?>
											<tr>
												<td><strong>#<?= $service->job_id ?></strong></td>
												<td><?= htmlspecialchars($service->customer_name) ?></td>
												<td><?= htmlspecialchars($service->vehicle_no ?: 'N/A') ?></td>
												<td><?= htmlspecialchars($service->description ?: 'Mechanical Service') ?></td>
												<td class="text-end"><strong>Rs. <?= number_format($service->final_bill_amount, 2) ?></strong></td>
												<td><?= date('H:i', strtotime($service->service_date)) ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
									<tfoot class="table-info">
										<tr>
											<th colspan="4" class="text-end">Total Mechanical Service Income:</th>
											<th class="text-end">Rs. <?= number_format(array_sum(array_column($mechanical_services ?: [], 'final_bill_amount')), 2) ?></th>
											<th></th>
										</tr>
									</tfoot>
								</table>
							</div>
						<?php else: ?>
							<div class="text-center py-4">
								<i class="fas fa-cog fa-3x text-muted mb-3"></i>
								<p class="text-muted">No Mechanical Service jobs found for this date.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Payment Details Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-info text-white">
						<h5 class="mb-0">
							<i class="fas fa-credit-card"></i> Payment Details
						</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<!-- Quick Bill Payments -->
							<div class="col-md-6">
								<h6 class="text-primary">Quick Bill Payments</h6>
								<?php if (!empty($payment_details['quick_bill_payments'])): ?>
									<div class="table-responsive">
										<table class="table table-sm">
											<thead>
												<tr>
													<th>Customer</th>
													<th>Method</th>
													<th>Amount</th>
													<th>Time</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($payment_details['quick_bill_payments'] as $payment): ?>
													<tr>
														<td><?= htmlspecialchars($payment->customer_name ?: 'Walk-in') ?></td>
														<td><span class="badge bg-secondary"><?= ucfirst($payment->payment_method) ?></span></td>
														<td class="text-end">Rs. <?= number_format($payment->paid_amount, 2) ?></td>
														<td><?= date('H:i', strtotime($payment->payment_date)) ?></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php else: ?>
									<p class="text-muted">No Quick Bill payments found.</p>
								<?php endif; ?>
							</div>

							<!-- Service Payments -->
							<div class="col-md-6">
								<h6 class="text-success">Service Payments</h6>
								<?php if (!empty($payment_details['service_payments'])): ?>
									<div class="table-responsive">
										<table class="table table-sm">
											<thead>
												<tr>
													<th>Customer</th>
													<th>Method</th>
													<th>Amount</th>
													<th>Time</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($payment_details['service_payments'] as $payment): ?>
													<tr>
														<td><?= htmlspecialchars($payment->customer_name) ?></td>
														<td><span class="badge bg-secondary"><?= ucfirst($payment->payment_method) ?></span></td>
														<td class="text-end">Rs. <?= number_format($payment->paid_amount, 2) ?></td>
														<td><?= date('H:i', strtotime($payment->payment_date)) ?></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php else: ?>
									<p class="text-muted">No Service payments found.</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Expense Details Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-danger text-white">
						<h5 class="mb-0">
							<i class="fas fa-money-bill-wave"></i> Expense Details
							<span class="badge bg-light text-dark ms-2"><?= count($expense_details) ?> expenses</span>
						</h5>
					</div>
					<div class="card-body">
						<?php if (!empty($expense_details)): ?>
							<div class="table-responsive">
								<table class="table table-striped table-hover">
									<thead class="table-dark">
										<tr>
											<th>Expense ID</th>
											<th>Category</th>
											<th>Description</th>
											<th>Amount</th>
											<th>Method</th>
											<th>Time</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($expense_details as $expense): ?>
											<tr>
												<td><strong>#<?= $expense->expense_id ?></strong></td>
												<td><span class="badge bg-info"><?= htmlspecialchars($expense->category_name ?: 'General') ?></span></td>
												<td><?= htmlspecialchars($expense->description ?: 'Expense') ?></td>
												<td class="text-end"><strong>Rs. <?= number_format($expense->amount, 2) ?></strong></td>
												<td><span class="badge bg-warning"><?= ucfirst($expense->payment_method) ?></span></td>
												<td><?= date('H:i', strtotime($expense->expense_date)) ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
									<tfoot class="table-danger">
										<tr>
											<th colspan="3" class="text-end">Total Expenses:</th>
											<th class="text-end">Rs. <?= number_format(array_sum(array_column($expense_details ?: [], 'amount')), 2) ?></th>
											<th colspan="2"></th>
										</tr>
									</tfoot>
								</table>
							</div>
						<?php else: ?>
							<div class="text-center py-4">
								<i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
								<p class="text-muted">No expenses found for this date.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Mechanic Outsource Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-dark text-white">
						<h5 class="mb-0">
							<i class="fas fa-cogs"></i> Mechanic Outsource Details
							<span class="badge bg-light text-dark ms-2"><?= count($mechanic_outsource) ?> items</span>
						</h5>
					</div>
					<div class="card-body">
						<?php if (!empty($mechanic_outsource)): ?>
							<div class="table-responsive">
								<table class="table table-striped table-hover">
									<thead class="table-dark">
										<tr>
											<th>Item</th>
											<th>Purchased From</th>
											<th>Customer</th>
											<th>Vehicle</th>
											<th>Buy Price</th>
											<th>Sell Price</th>
											<th>Profit</th>
											<th>Job ID</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($mechanic_outsource as $item): ?>
											<tr>
												<td><?= htmlspecialchars($item->item_name) ?></td>
												<td><?= htmlspecialchars($item->purchased_from) ?></td>
												<td><?= htmlspecialchars($item->customer_name) ?></td>
												<td><?= htmlspecialchars($item->vehicle_no ?: 'N/A') ?></td>
												<td class="text-end">Rs. <?= number_format($item->purchased_price, 2) ?></td>
												<td class="text-end">Rs. <?= number_format($item->invoice_price, 2) ?></td>
												<td class="text-end text-success"><strong>Rs. <?= number_format($item->invoice_price - $item->purchased_price, 2) ?></strong></td>
												<td><strong>#<?= $item->job_id ?></strong></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
									<tfoot class="table-info">
										<tr>
											<th colspan="4" class="text-end">Total Buy Cost:</th>
											<th class="text-end">Rs. <?= number_format(array_sum(array_column($mechanic_outsource ?: [], 'purchased_price')), 2) ?></th>
											<th class="text-end">Rs. <?= number_format(array_sum(array_column($mechanic_outsource ?: [], 'invoice_price')), 2) ?></th>
											<th class="text-end">Rs. <?= number_format(array_sum(array_column($mechanic_outsource ?: [], 'invoice_price')) - array_sum(array_column($mechanic_outsource ?: [], 'purchased_price')), 2) ?></th>
											<th></th>
										</tr>
									</tfoot>
								</table>
							</div>
						<?php else: ?>
							<div class="text-center py-4">
								<i class="fas fa-cogs fa-3x text-muted mb-3"></i>
								<p class="text-muted">No mechanic outsource items found for this date.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Balance Transactions Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-secondary text-white">
						<h5 class="mb-0">
							<i class="fas fa-wallet"></i> Balance Transactions
						</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<!-- Cash in Hand Transactions -->
							<div class="col-md-4">
								<h6 class="text-primary">Cash in Hand</h6>
								<?php if (!empty($balance_transactions['in_hand'])): ?>
									<div class="table-responsive">
										<table class="table table-sm">
											<thead>
												<tr>
													<th>Type</th>
													<th>Amount</th>
													<th>Balance</th>
													<th>Time</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($balance_transactions['in_hand'] as $txn): ?>
													<tr>
														<td><span class="badge bg-<?= $txn->txn_type == 'credit' ? 'success' : 'danger' ?>"><?= ucfirst($txn->txn_type) ?></span></td>
														<td class="text-end">Rs. <?= number_format($txn->amount, 2) ?></td>
														<td class="text-end">Rs. <?= number_format($txn->running_balance, 2) ?></td>
														<td><?= date('H:i', strtotime($txn->created_at)) ?></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php else: ?>
									<p class="text-muted">No cash transactions found.</p>
								<?php endif; ?>
							</div>

							<!-- Bank Transactions -->
							<div class="col-md-4">
								<h6 class="text-info">Bank Account</h6>
								<?php if (!empty($balance_transactions['bank'])): ?>
									<div class="table-responsive">
										<table class="table table-sm">
											<thead>
												<tr>
													<th>Type</th>
													<th>Amount</th>
													<th>Balance</th>
													<th>Time</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($balance_transactions['bank'] as $txn): ?>
													<tr>
														<td><span class="badge bg-<?= $txn->txn_type == 'credit' ? 'success' : 'danger' ?>"><?= ucfirst($txn->txn_type) ?></span></td>
														<td class="text-end">Rs. <?= number_format($txn->amount, 2) ?></td>
														<td class="text-end">Rs. <?= number_format($txn->running_balance, 2) ?></td>
														<td><?= date('H:i', strtotime($txn->created_at)) ?></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php else: ?>
									<p class="text-muted">No bank transactions found.</p>
								<?php endif; ?>
							</div>

							<!-- Card Machine Transactions -->
							<div class="col-md-4">
								<h6 class="text-warning">Card Machine</h6>
								<?php if (!empty($balance_transactions['card_machine'])): ?>
									<div class="table-responsive">
										<table class="table table-sm">
											<thead>
												<tr>
													<th>Type</th>
													<th>Amount</th>
													<th>Balance</th>
													<th>Time</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($balance_transactions['card_machine'] as $txn): ?>
													<tr>
														<td><span class="badge bg-<?= $txn->txn_type == 'credit' ? 'success' : 'danger' ?>"><?= ucfirst($txn->txn_type) ?></span></td>
														<td class="text-end">Rs. <?= number_format($txn->amount, 2) ?></td>
														<td class="text-end">Rs. <?= number_format($txn->running_balance, 2) ?></td>
														<td><?= date('H:i', strtotime($txn->created_at)) ?></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php else: ?>
									<p class="text-muted">No card transactions found.</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Profit Analysis Section -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header bg-success text-white">
						<h5 class="mb-0">
							<i class="fas fa-chart-pie"></i> Profit Analysis
						</h5>
					</div>
					<div class="card-body">
						<div class="row">
							<!-- Quick Bill Profits -->
							<div class="col-md-6">
								<h6 class="text-primary">Quick Bill Profits</h6>
								<?php if (!empty($profit_details['quick_bill_profits'])): ?>
									<div class="table-responsive">
										<table class="table table-sm">
											<thead>
												<tr>
													<th>Bill ID</th>
													<th>Customer</th>
													<th>Revenue</th>
													<th>COGS</th>
													<th>Profit</th>
													<th>Margin %</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($profit_details['quick_bill_profits'] as $profit): ?>
													<?php 
														$profit_amount = $profit->final_bill_amount - $profit->cogs;
														$margin = $profit->final_bill_amount > 0 ? ($profit_amount / $profit->final_bill_amount) * 100 : 0;
													?>
													<tr>
														<td><strong>#<?= $profit->quick_bill_id ?></strong></td>
														<td><?= htmlspecialchars($profit->customer_name ?: 'Walk-in') ?></td>
														<td class="text-end">Rs. <?= number_format($profit->final_bill_amount, 2) ?></td>
														<td class="text-end">Rs. <?= number_format($profit->cogs, 2) ?></td>
														<td class="text-end text-success"><strong>Rs. <?= number_format($profit_amount, 2) ?></strong></td>
														<td class="text-end"><?= number_format($margin, 1) ?>%</td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php else: ?>
									<p class="text-muted">No Quick Bill profit data available.</p>
								<?php endif; ?>
							</div>

							<!-- Service Profits -->
							<div class="col-md-6">
								<h6 class="text-success">Service Job Profits</h6>
								<?php if (!empty($profit_details['service_profits'])): ?>
									<div class="table-responsive">
										<table class="table table-sm">
											<thead>
												<tr>
													<th>Job ID</th>
													<th>Customer</th>
													<th>Vehicle</th>
													<th>Revenue</th>
													<th>COGS</th>
													<th>Profit</th>
													<th>Margin %</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($profit_details['service_profits'] as $profit): ?>
													<?php 
														$profit_amount = $profit->final_bill_amount - $profit->cogs;
														$margin = $profit->final_bill_amount > 0 ? ($profit_amount / $profit->final_bill_amount) * 100 : 0;
													?>
													<tr>
														<td><strong>#<?= $profit->job_id ?></strong></td>
														<td><?= htmlspecialchars($profit->customer_name) ?></td>
														<td><?= htmlspecialchars($profit->vehicle_no ?: 'N/A') ?></td>
														<td class="text-end">Rs. <?= number_format($profit->final_bill_amount, 2) ?></td>
														<td class="text-end">Rs. <?= number_format($profit->cogs, 2) ?></td>
														<td class="text-end text-success"><strong>Rs. <?= number_format($profit_amount, 2) ?></strong></td>
														<td class="text-end"><?= number_format($margin, 1) ?>%</td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php else: ?>
									<p class="text-muted">No Service profit data available.</p>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Summary Card -->
		<div class="row">
			<div class="col-12">
				<div class="card border-primary">
					<div class="card-header bg-primary text-white">
						<h5 class="mb-0">
							<i class="fas fa-chart-line"></i> Daily Summary
						</h5>
					</div>
					<div class="card-body">
						<div class="row text-center">
							<div class="col-md-2">
								<div class="border rounded p-3">
									<h6 class="text-primary">Quick Bills</h6>
									<h4 class="text-primary">Rs. <?= number_format(array_sum(array_column($quick_bills ?: [], 'final_bill_amount')), 2) ?></h4>
									<small class="text-muted"><?= count($quick_bills ?: []) ?> transactions</small>
								</div>
							</div>
							<div class="col-md-2">
								<div class="border rounded p-3">
									<h6 class="text-success">Normal Services</h6>
									<h4 class="text-success">Rs. <?= number_format(array_sum(array_column($normal_services ?: [], 'final_bill_amount')), 2) ?></h4>
									<small class="text-muted"><?= count($normal_services ?: []) ?> jobs</small>
								</div>
							</div>
							<div class="col-md-2">
								<div class="border rounded p-3">
									<h6 class="text-warning">Mechanical Services</h6>
									<h4 class="text-warning">Rs. <?= number_format(array_sum(array_column($mechanical_services ?: [], 'final_bill_amount')), 2) ?></h4>
									<small class="text-muted"><?= count($mechanical_services ?: []) ?> jobs</small>
								</div>
							</div>
							<div class="col-md-2">
								<div class="border rounded p-3">
									<h6 class="text-dark">Mechanic Outsource</h6>
									<h4 class="text-dark">Rs. <?= number_format(array_sum(array_column($mechanic_outsource ?: [], 'invoice_price')) - array_sum(array_column($mechanic_outsource ?: [], 'purchased_price')), 2) ?></h4>
									<small class="text-muted"><?= count($mechanic_outsource ?: []) ?> items</small>
								</div>
							</div>
							<div class="col-md-2">
								<div class="border rounded p-3">
									<h6 class="text-danger">Total Expenses</h6>
									<h4 class="text-danger">Rs. <?= number_format(array_sum(array_column($expense_details ?: [], 'amount')), 2) ?></h4>
									<small class="text-muted"><?= count($expense_details ?: []) ?> expenses</small>
								</div>
							</div>
							<div class="col-md-2">
								<div class="border rounded p-3">
									<h6 class="text-info">Total Profit</h6>
									<?php 
										$total_revenue = array_sum(array_column($quick_bills ?: [], 'final_bill_amount')) + 
														array_sum(array_column($normal_services ?: [], 'final_bill_amount')) + 
														array_sum(array_column($mechanical_services ?: [], 'final_bill_amount'));
										$total_cogs = array_sum(array_column($profit_details['quick_bill_profits'] ?: [], 'cogs')) + 
													array_sum(array_column($profit_details['service_profits'] ?: [], 'cogs'));
										$total_profit = $total_revenue - $total_cogs - array_sum(array_column($expense_details ?: [], 'amount'));
									?>
									<h4 class="text-info">Rs. <?= number_format($total_profit, 2) ?></h4>
									<small class="text-muted">Net profit</small>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
@media print {
	.btn, .card-header .badge {
		display: none !important;
	}
	.card {
		border: 1px solid #000 !important;
		page-break-inside: avoid;
	}
	.table {
		font-size: 12px;
	}
}
</style>
