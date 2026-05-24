<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Accounts - Loan</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Loan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Current Balance</h5>
            </div>
            <div class="card-body text-center">
                <h1 class="display-5" style="color:#0ab39c; font-weight:800;">LKR <?= number_format($account->balance ?? 0, 2) ?></h1>
                <div class="text-muted">as of <?= date('Y-m-d H:i') ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Loan Transactions</h5>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="loanToggle" id="loanIn" autocomplete="off" checked>
                    <label class="btn btn-outline-primary" for="loanIn">In</label>

                    <input type="radio" class="btn-check" name="loanToggle" id="loanOut" autocomplete="off">
                    <label class="btn btn-outline-primary" for="loanOut">Out</label>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success" id="btnAddIn" style="display: block;">Add IN</button>
                            <button type="button" class="btn btn-danger" id="btnAddOut" style="display: none;">Add OUT</button>
                        </div>
                    </div>
                </div>
                <div id="loanTableIn">
                    <table id="tableLoanIn" class="table table-striped table-responsive table-sm">
                        <thead class="table-info">
                            <tr>
                                <th>ID</th>
                                <th>Company / Name</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Running Balance</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Returned To</th>
                                <th class="text-center">Return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; foreach($txns_in as $t): ?>
                            <tr>
                                <td><?= $t->txn_id ?></td>
                                <td><?= htmlspecialchars($t->company_name ?? '-') ?></td>
                                <td><?= htmlspecialchars($t->description) ?></td>
                                <td><span class="badge <?= $t->txn_type=='credit' ? 'bg-success':'bg-danger' ?>"><?= strtoupper($t->txn_type) ?></span></td>
                                <td class="text-end"><?= number_format($t->amount, 2) ?></td>
                                <td class="text-end">
                                    <?php if (isset($t->running_balance)): ?>
                                        <strong class="<?= $t->running_balance >= 0 ? 'text-success' : 'text-danger' ?>">LKR <?= number_format($t->running_balance, 2) ?></strong>
                                    <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                </td>
                                <td class="text-center"><?= date('Y-m-d H:i', strtotime($t->created_at)) ?></td>
                                <td class="text-center">
                                    <?php if (!empty($t->parent_txn_id)): ?>
                                        <span class="badge bg-info">OUT #<?= $t->parent_txn_id ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">-</td>
                            </tr>
                            <?php endforeach; if (empty($txns_in)): ?>
                            <tr>
                                <td>1</td>
                                <td class="text-muted">No transactions found</td>
                                <td class="text-muted">-</td>
                                <td class="text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-center text-muted">-</td>
                                <td class="text-center text-muted">-</td>
                                <td class="text-center text-muted">-</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div id="loanTableOut" style="display:none;">
                    <table id="tableLoanOut" class="table table-striped table-responsive table-sm">
                        <thead class="table-warning">
                            <tr>
                                <th>ID</th>
                                <th>Company / Name</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Running Balance</th>
                                <th class="text-center">Date</th>
                                <th class="text-end">Returned</th>
                                <th class="text-center">Return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $j=1; foreach($txns_out as $t): ?>
                            <tr>
                                <td><?= $t->txn_id ?></td>
                                <td><?= htmlspecialchars($t->company_name ?? '-') ?></td>
                                <td><?= htmlspecialchars($t->description) ?></td>
                                <td><span class="badge <?= $t->txn_type=='credit' ? 'bg-success':'bg-danger' ?>"><?= strtoupper($t->txn_type) ?></span></td>
                                <td class="text-end"><?= number_format($t->amount, 2) ?></td>
                                <td class="text-end">
                                    <?php if (isset($t->running_balance)): ?>
                                        <strong class="<?= $t->running_balance >= 0 ? 'text-success' : 'text-danger' ?>">LKR <?= number_format($t->running_balance, 2) ?></strong>
                                    <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                </td>
                                <td class="text-center"><?= date('Y-m-d H:i', strtotime($t->created_at)) ?></td>
                                <td class="text-end">
                                    <?php if (!empty($t->returned_total) && $t->returned_total > 0): ?>
                                        <span class="badge bg-success">LKR <?= number_format($t->returned_total, 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary btnReturnLoan" data-id="<?= $t->txn_id ?>">Return</button>
                                </td>
                            </tr>
                            <?php endforeach; if (empty($txns_out)): ?>
                            <tr>
                                <td>1</td>
                                <td class="text-muted">No transactions found</td>
                                <td class="text-muted">-</td>
                                <td class="text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-center text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-center text-muted">-</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    function initDT(id){
        if ($.fn.DataTable) {
            var hasRealRows = $(id+' tbody tr').length > 0 && $(id+' tbody tr td').eq(1).text().trim() !== 'No transactions found';
            if (hasRealRows) {
                if ($.fn.DataTable.isDataTable(id)) $(id).DataTable().destroy();
                $(id).DataTable({ lengthMenu: [[10,25,50,-1],[10,25,50,'All']], searching: true, responsive: true });
            }
        }
    }
    initDT('#tableLoanIn');
    initDT('#tableLoanOut');

    $('#loanIn').on('change', function(){ 
        if(this.checked){ 
            $('#loanTableIn').show(); 
            $('#loanTableOut').hide(); 
            $('#btnAddIn').show(); 
            $('#btnAddOut').hide(); 
        } 
    });
    $('#loanOut').on('change', function(){ 
        if(this.checked){ 
            $('#loanTableOut').show(); 
            $('#loanTableIn').hide(); 
            $('#btnAddIn').hide(); 
            $('#btnAddOut').show(); 
        } 
    });

    function submitLoan(direction){
        var amount = parseFloat($('#mdl_amount_'+direction).val());
        var company = $('#mdl_company_'+direction).val();
        var expected = $('#mdl_expected_'+direction).val();
        var desc = $('#mdl_desc_'+direction).val();
        if(!amount || amount <= 0){ Swal.fire('Error','Enter amount','error'); return; }
        if(!company || company.trim() === ''){ Swal.fire('Error','Company / Name is required','error'); return; }
        $.post("<?= base_url('accounts/loan_add'); ?>", { direction: direction, amount: amount, company_name: company, expected_return_date: expected, description: desc }, function(res){
            if(res.status==='success'){ Toastify({ text: 'Saved', className: 'toastify-success', duration: 3000 }).showToast(); location.reload(); }
            else{ Swal.fire('Error', res.message || 'Failed', 'error'); }
        }, 'json');
    }
    $(document).on('click', '#btnAddIn', function(e){ e.preventDefault(); $('#modal_in').modal('show'); });
    $(document).on('click', '#btnAddOut', function(e){ e.preventDefault(); $('#modal_out').modal('show'); });
    $(document).on('click', '#save_in', function(e){ e.preventDefault(); submitLoan('in'); });
    $(document).on('click', '#save_out', function(e){ e.preventDefault(); submitLoan('out'); });

    $(document).on('click', '.btnReturnLoan', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var companyName = $(this).closest('tr').find('td:eq(1)').text();
        var originalAmount = parseFloat($(this).closest('tr').find('td:eq(4)').text().replace(/[^\d.-]/g, ''));
        var returnedAmount = parseFloat($(this).closest('tr').find('td:eq(7)').text().replace(/[^\d.-]/g, '')) || 0;
        
        if (returnedAmount >= originalAmount) {
            Swal.fire('Error', 'This loan is already fully paid back!', 'error');
            return;
        }
        
        $('#mdl_parent_id').val(id);
        $('#mdl_company_return').val(companyName);
        $('#modal_return').modal('show');
    });
    $(document).on('click', '#save_return', function(e){
        e.preventDefault();
        var pid = parseInt($('#mdl_parent_id').val());
        var amount = parseFloat($('#mdl_amount_return').val());
        var desc = $('#mdl_desc_return').val();
        if(!amount || amount <= 0){ Swal.fire('Error','Enter amount','error'); return; }
        if(!pid || pid <= 0){ Swal.fire('Error','Invalid loan ID','error'); return; }
        console.log('Sending return data:', { parent_txn_id: pid, amount: amount, description: desc });
        $.post("<?= base_url('accounts/loan_return'); ?>", { parent_txn_id: pid, amount: amount, description: desc }, function(res){
            console.log('Return response:', res);
            if(res.status==='success'){ Toastify({ text: 'Saved', className: 'toastify-success', duration: 3000 }).showToast(); location.reload(); }
            else{ Swal.fire('Error', res.message || 'Failed', 'error'); }
        }, 'json').fail(function(jq){ 
            console.log('Return error:', jq); 
            console.log('Status:', jq.status);
            console.log('Response text:', jq.responseText);
            Swal.fire('Error', 'Status: ' + jq.status + ' - ' + (jq.responseText || 'Request failed'), 'error'); 
        });
    });
})();
</script>

<!-- IN Modal -->
<div class="modal fade" id="modal_in" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Loan IN</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label>Amount</label><input type="number" min="0" step="0.01" class="form-control" id="mdl_amount_in"></div>
        <div class="mb-2"><label>Company / Name *</label><input type="text" class="form-control" id="mdl_company_in" required></div>
        <div class="mb-2"><label>Expected Return Date</label><input type="date" class="form-control" id="mdl_expected_in"></div>
        <div class="mb-2"><label>Description</label><textarea class="form-control" id="mdl_desc_in"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-success" id="save_in">Save</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
  </div>

<!-- OUT Modal -->
<div class="modal fade" id="modal_out" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Loan OUT</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label>Amount</label><input type="number" min="0" step="0.01" class="form-control" id="mdl_amount_out"></div>
        <div class="mb-2"><label>Company / Name *</label><input type="text" class="form-control" id="mdl_company_out" required></div>
        <div class="mb-2"><label>Expected Return Date</label><input type="date" class="form-control" id="mdl_expected_out"></div>
        <div class="mb-2"><label>Description</label><textarea class="form-control" id="mdl_desc_out"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-danger" id="save_out">Save</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
  </div>

<!-- Return Modal -->
<div class="modal fade" id="modal_return" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Record Return</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="mdl_parent_id">
        <div class="mb-2"><label>Company / Name</label><input type="text" class="form-control" id="mdl_company_return" readonly></div>
        <div class="mb-2"><label>Amount</label><input type="number" min="0" step="0.01" class="form-control" id="mdl_amount_return"></div>
        <div class="mb-2"><label>Description</label><textarea class="form-control" id="mdl_desc_return"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-primary" id="save_return">Save</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
  </div>


