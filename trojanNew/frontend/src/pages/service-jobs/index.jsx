import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { Plus, Eye, CheckCircle, Trash2, Play, Clock, Receipt } from 'lucide-react'
import api from '@/lib/api'
import { formatCurrency, formatDate } from '@/lib/utils'
import { useAuthStore } from '@/store/authStore'
import { toast } from '@/hooks/use-toast'
import PageHeader from '@/components/shared/PageHeader'
import DataTable from '@/components/shared/DataTable'
import ConfirmDialog from '@/components/shared/ConfirmDialog'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select'

function statusBadge(status) {
  const map = {
    estimate:         'secondary',
    open:             'info',
    in_progress:      'warning',
    waiting_for_parts: 'destructive',
    completed:        'success',
    invoiced:         'default',
  }
  return <Badge variant={map[status] ?? 'secondary'}>{status?.replace(/_/g, ' ')}</Badge>
}

// ─── New Job Modal ────────────────────────────────────────────────────────────
function NewJobModal({ open, onOpenChange, customers, vehicles, users, onSave, saving }) {
  const [customerId,         setCustomerId]         = useState('')
  const [vehicleId,          setVehicleId]          = useState('')
  const [technicianId,       setTechnicianId]       = useState('')
  const [serviceType,        setServiceType]        = useState('normal')
  const [mileageIn,          setMileageIn]          = useState('')
  const [customerComplaint,  setCustomerComplaint]  = useState('')
  const [laborDescription,   setLaborDescription]   = useState('')
  const [laborCost,          setLaborCost]          = useState('')
  const [notes,              setNotes]              = useState('')

  const customerVehicles = vehicles.filter((v) => v.customer_id === customerId)
  const technicians      = users.filter((u) => ['technician', 'admin', 'manager'].includes(u.role))

  function reset() {
    setCustomerId(''); setVehicleId(''); setTechnicianId('')
    setServiceType('normal'); setMileageIn(''); setCustomerComplaint('')
    setLaborDescription(''); setLaborCost(''); setNotes('')
  }

  function handleOpen(isOpen) {
    if (isOpen) reset()
    onOpenChange(isOpen)
  }

  function handleSubmit(e) {
    e.preventDefault()
    onSave({
      customer_id:        customerId        || null,
      vehicle_id:         vehicleId         || null,
      technician_id:      technicianId      || null,
      service_type:       serviceType,
      mileage_in:         mileageIn         ? Number(mileageIn)  : null,
      customer_complaint: customerComplaint || null,
      labor_description:  laborDescription  || null,
      labor_cost:         laborCost         ? Number(laborCost)  : 0,
      notes:              notes             || null,
    })
  }

  return (
    <Dialog open={open} onOpenChange={handleOpen}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader><DialogTitle>New Service Job</DialogTitle></DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">

          {/* Service Type */}
          <div>
            <Label>Service Type</Label>
            <div className="flex gap-2 mt-1">
              {['normal', 'mechanical', 'estimate'].map((t) => (
                <button
                  key={t}
                  type="button"
                  onClick={() => setServiceType(t)}
                  className={`px-3 py-1.5 text-sm rounded-md border transition-colors capitalize ${
                    serviceType === t
                      ? 'bg-primary text-primary-foreground border-primary'
                      : 'bg-background border-input hover:bg-muted'
                  }`}
                >
                  {t}
                </button>
              ))}
            </div>
            {serviceType === 'estimate' && (
              <p className="text-xs text-muted-foreground mt-1">
                Job will be created in <strong>Estimate</strong> status — no stock is affected until the job is opened.
              </p>
            )}
          </div>

          <div className="grid grid-cols-2 gap-3">
            {/* Customer */}
            <div>
              <Label>Customer</Label>
              <Select value={customerId} onValueChange={(v) => { setCustomerId(v); setVehicleId('') }}>
                <SelectTrigger className="mt-1"><SelectValue placeholder="Select customer…" /></SelectTrigger>
                <SelectContent>
                  {customers.map((c) => <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>)}
                </SelectContent>
              </Select>
            </div>

            {/* Vehicle */}
            <div>
              <Label>Vehicle</Label>
              <Select value={vehicleId} onValueChange={setVehicleId} disabled={!customerId}>
                <SelectTrigger className="mt-1"><SelectValue placeholder="Select vehicle…" /></SelectTrigger>
                <SelectContent>
                  {customerVehicles.map((v) => (
                    <SelectItem key={v.id} value={v.id}>
                      {v.registration_number ?? v.plate_number} — {[v.make, v.model].filter(Boolean).join(' ')}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {/* Technician */}
            <div>
              <Label>Assigned Technician</Label>
              <Select value={technicianId} onValueChange={setTechnicianId}>
                <SelectTrigger className="mt-1"><SelectValue placeholder="Select technician…" /></SelectTrigger>
                <SelectContent>
                  {technicians.map((u) => (
                    <SelectItem key={u.id} value={u.id}>{u.full_name ?? u.email} ({u.role})</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {/* Mileage In */}
            <div>
              <Label>Mileage In (km)</Label>
              <Input
                type="number" min="0" placeholder="e.g. 45 000"
                value={mileageIn} onChange={(e) => setMileageIn(e.target.value)}
                className="mt-1"
              />
            </div>
          </div>

          {/* Customer Complaint */}
          <div>
            <Label>Customer Complaint / Symptom</Label>
            <Textarea
              placeholder="What the customer reported — e.g. 'engine noise at startup'"
              value={customerComplaint}
              onChange={(e) => setCustomerComplaint(e.target.value)}
              rows={2} className="mt-1"
            />
          </div>

          <div className="grid grid-cols-2 gap-3">
            {/* Labor Description */}
            <div>
              <Label>Labor Description</Label>
              <Textarea
                placeholder="Work to be performed"
                value={laborDescription}
                onChange={(e) => setLaborDescription(e.target.value)}
                rows={2} className="mt-1"
              />
            </div>

            {/* Internal Notes */}
            <div>
              <Label>Internal Notes</Label>
              <Textarea
                placeholder="Internal notes (not shown to customer)"
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
                rows={2} className="mt-1"
              />
            </div>
          </div>

          {/* Estimated Labor Cost */}
          <div className="w-1/2">
            <Label>Estimated Labor Cost</Label>
            <Input
              type="number" min="0" step="0.01" placeholder="0.00"
              value={laborCost} onChange={(e) => setLaborCost(e.target.value)}
              className="mt-1"
            />
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
            <Button type="submit" disabled={saving}>{saving ? 'Saving…' : 'Create Job'}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}

// ─── Add Item Modal ───────────────────────────────────────────────────────────
function AddItemModal({ open, onOpenChange, products, inventory, onSave, saving }) {
  const [productId, setProductId] = useState('')
  const [qty,       setQty]       = useState(1)

  function handleOpen(isOpen) {
    if (isOpen) { setProductId(''); setQty(1) }
    onOpenChange(isOpen)
  }

  const stockItem    = inventory.find((i) => i.product_id === productId)
  const currentStock = stockItem?.qty_in_stock ?? null
  const outOfStock   = currentStock !== null && currentStock === 0
  const overQty      = currentStock !== null && Number(qty) > currentStock && !outOfStock

  function handleSubmit(e) {
    e.preventDefault()
    if (!stockItem) return
    onSave({
      inventory_id: stockItem.id,
      product_id:   productId,
      qty_used:     Number(qty),
      unit_price:   stockItem.selling_price,
    })
  }

  return (
    <Dialog open={open} onOpenChange={handleOpen}>
      <DialogContent className="max-w-sm">
        <DialogHeader><DialogTitle>Add Item to Job</DialogTitle></DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-3">
          <div>
            <Label>Product *</Label>
            <Select value={productId} onValueChange={setProductId}>
              <SelectTrigger className="mt-1"><SelectValue placeholder="Select product…" /></SelectTrigger>
              <SelectContent>
                {products.filter((p) => p.is_active !== false).map((p) => (
                  <SelectItem key={p.id} value={p.id}>{p.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            {currentStock !== null && (
              outOfStock ? (
                <p className="text-xs text-destructive mt-1 font-medium">
                  Out of stock — create a Purchase Order to restock this part first.
                </p>
              ) : (
                <p className="text-xs text-muted-foreground mt-1">
                  Stock available: <strong>{currentStock}</strong>
                  {stockItem?.selling_price != null && (
                    <> · Unit price: <strong>{formatCurrency(stockItem.selling_price)}</strong></>
                  )}
                </p>
              )
            )}
          </div>

          <div>
            <Label>Quantity *</Label>
            <Input
              type="number" min="1"
              max={currentStock ?? undefined}
              value={qty}
              onChange={(e) => setQty(e.target.value)}
              required className="mt-1"
            />
            {overQty && (
              <p className="text-xs text-destructive mt-1">
                Quantity exceeds available stock ({currentStock}).
              </p>
            )}
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
            <Button
              type="submit"
              disabled={saving || !productId || outOfStock || overQty}
            >
              {saving ? 'Adding…' : 'Add Item'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}

// ─── Job Detail Modal ─────────────────────────────────────────────────────────
function JobDetailModal({ job, open, onOpenChange, products, inventory }) {
  const qc = useQueryClient()
  const { role } = useAuthStore()
  const [addItemModal, setAddItemModal] = useState(false)
  const [removeTarget, setRemoveTarget] = useState(null)

  const { data: detail } = useQuery({
    queryKey: ['service-job', job?.id],
    queryFn:  () => api.get(`/service-jobs/${job.id}`),
    enabled:  !!job?.id,
  })

  const addItemMutation = useMutation({
    mutationFn: (data) => api.post(`/service-jobs/${job.id}/items`, data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['service-job', job.id] })
      qc.invalidateQueries({ queryKey: ['inventory'] })
      setAddItemModal(false)
      toast({ title: 'Item added', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const removeItemMutation = useMutation({
    mutationFn: (itemId) => api.delete(`/service-jobs/${job.id}/items/${itemId}`),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['service-job', job.id] })
      qc.invalidateQueries({ queryKey: ['inventory'] })
      setRemoveTarget(null)
      toast({ title: 'Item removed — stock restored', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const statusMutation = useMutation({
    mutationFn: (status) => api.put(`/service-jobs/${job.id}/status`, { status }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['service-jobs'] })
      qc.invalidateQueries({ queryKey: ['service-job', job.id] })
      toast({ title: 'Status updated', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const j          = detail ?? job
  const items      = j?.service_job_items ?? j?.items ?? []
  const partsTotal = items.reduce((s, it) => s + (Number(it.qty_used) || 0) * (Number(it.unit_price) || 0), 0)
  const laborTotal = Number(j?.labor_cost) || 0
  const grandTotal = partsTotal + laborTotal

  const canManage = ['admin', 'manager'].includes(role)
  const canAct    = ['admin', 'manager', 'technician'].includes(role)
  const canModify = canAct && ['open', 'in_progress'].includes(j?.status)

  // Derive which status-transition buttons to show
  const statusActions = []
  if (j?.status === 'estimate' && canAct)
    statusActions.push({ label: 'Open Job',           nextStatus: 'open',             Icon: CheckCircle, variant: 'default'  })
  if (j?.status === 'open' && canAct) {
    statusActions.push({ label: 'Start Job',          nextStatus: 'in_progress',      Icon: Play,        variant: 'default'  })
    statusActions.push({ label: 'Waiting for Parts',  nextStatus: 'waiting_for_parts',Icon: Clock,       variant: 'outline'  })
  }
  if (j?.status === 'in_progress' && canAct) {
    statusActions.push({ label: 'Waiting for Parts',  nextStatus: 'waiting_for_parts',Icon: Clock,       variant: 'outline'  })
    if (canManage)
      statusActions.push({ label: 'Mark Complete',    nextStatus: 'completed',        Icon: CheckCircle, variant: 'default'  })
  }
  if (j?.status === 'waiting_for_parts' && canAct)
    statusActions.push({ label: 'Resume Job',         nextStatus: 'in_progress',      Icon: Play,        variant: 'default'  })
  if (j?.status === 'completed' && canManage)
    statusActions.push({ label: 'Mark Invoiced',      nextStatus: 'invoiced',         Icon: Receipt,     variant: 'default'  })

  if (!job) return null

  return (
    <>
      <Dialog open={open} onOpenChange={onOpenChange}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>Job {j?.job_number ?? j?.id?.slice(0, 8)}</DialogTitle>
          </DialogHeader>
          <div className="space-y-4 text-sm">

            {/* Header info grid */}
            <div className="grid grid-cols-2 gap-2">
              <div><span className="text-muted-foreground">Customer:</span> <span className="font-medium">{j?.customers?.name ?? '—'}</span></div>
              <div className="flex items-center gap-2"><span className="text-muted-foreground">Status:</span> {statusBadge(j?.status)}</div>
              <div><span className="text-muted-foreground">Vehicle:</span> <span className="font-medium">{j?.vehicles?.registration_number ?? '—'}</span></div>
              <div><span className="text-muted-foreground">Opened:</span> {formatDate(j?.created_at)}</div>
              {j?.service_type && (
                <div><span className="text-muted-foreground">Type:</span> <span className="font-medium capitalize">{j.service_type}</span></div>
              )}
              {j?.mileage_in != null && (
                <div><span className="text-muted-foreground">Mileage In:</span> <span className="font-medium">{Number(j.mileage_in).toLocaleString()} km</span></div>
              )}
            </div>

            {/* Customer complaint */}
            {j?.customer_complaint && (
              <div className="rounded-md bg-muted/50 border px-3 py-2">
                <p className="text-xs text-muted-foreground font-medium mb-0.5">Customer Complaint</p>
                <p className="italic">{j.customer_complaint}</p>
              </div>
            )}

            {/* Parts table */}
            <div>
              <div className="flex items-center justify-between mb-2">
                <span className="font-semibold">Parts Used</span>
                {canModify && (
                  <Button size="sm" variant="outline" onClick={() => setAddItemModal(true)}>
                    <Plus className="h-3.5 w-3.5 mr-1" /> Add Item
                  </Button>
                )}
              </div>
              <div className="rounded-md border overflow-hidden">
                <table className="w-full text-xs">
                  <thead className="bg-muted/50">
                    <tr>
                      <th className="px-3 py-2 text-left font-medium text-muted-foreground">Product</th>
                      <th className="px-3 py-2 text-right font-medium text-muted-foreground">Qty</th>
                      <th className="px-3 py-2 text-right font-medium text-muted-foreground">Unit Price</th>
                      <th className="px-3 py-2 text-right font-medium text-muted-foreground">Subtotal</th>
                      {canModify && <th className="px-3 py-2 w-8" />}
                    </tr>
                  </thead>
                  <tbody>
                    {items.length === 0 ? (
                      <tr>
                        <td colSpan={canModify ? 5 : 4} className="px-3 py-4 text-center text-muted-foreground">
                          No items yet.
                        </td>
                      </tr>
                    ) : items.map((it) => (
                      <tr key={it.id} className="border-t">
                        <td className="px-3 py-2">{it.products?.name ?? it.product_id}</td>
                        <td className="px-3 py-2 text-right">{it.qty_used}</td>
                        <td className="px-3 py-2 text-right">{formatCurrency(it.unit_price)}</td>
                        <td className="px-3 py-2 text-right">{formatCurrency(it.qty_used * it.unit_price)}</td>
                        {canModify && (
                          <td className="px-3 py-2">
                            <button
                              onClick={() => setRemoveTarget(it)}
                              className="text-destructive hover:opacity-80"
                            >
                              <Trash2 className="h-3.5 w-3.5" />
                            </button>
                          </td>
                        )}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            {/* Cost summary */}
            <div className="rounded-md border divide-y text-sm">
              <div className="flex justify-between px-3 py-2 text-muted-foreground">
                <span>Parts subtotal</span>
                <span>{formatCurrency(partsTotal)}</span>
              </div>
              <div className="flex justify-between px-3 py-2 text-muted-foreground">
                <div>
                  <span>Labor</span>
                  {j?.labor_description && (
                    <p className="text-xs italic">{j.labor_description}</p>
                  )}
                </div>
                <span>{formatCurrency(laborTotal)}</span>
              </div>
              <div className="flex justify-between px-3 py-2 font-semibold">
                <span>Total</span>
                <span>{formatCurrency(grandTotal)}</span>
              </div>
            </div>

            {/* Internal notes */}
            {j?.notes && (
              <p className="text-xs text-muted-foreground italic">{j.notes}</p>
            )}

            {/* Status action buttons */}
            {statusActions.length > 0 && (
              <div className="flex gap-2 flex-wrap pt-1">
                {statusActions.map(({ label, nextStatus, Icon, variant }) => (
                  <Button
                    key={nextStatus}
                    variant={variant}
                    size="sm"
                    className="gap-1.5"
                    onClick={() => statusMutation.mutate(nextStatus)}
                    disabled={statusMutation.isPending}
                  >
                    <Icon className="h-3.5 w-3.5" />
                    {label}
                  </Button>
                ))}
              </div>
            )}
          </div>
        </DialogContent>
      </Dialog>

      <AddItemModal
        open={addItemModal}
        onOpenChange={setAddItemModal}
        products={products}
        inventory={inventory}
        onSave={(data) => addItemMutation.mutate(data)}
        saving={addItemMutation.isPending}
      />

      <ConfirmDialog
        open={!!removeTarget}
        onOpenChange={(open) => !open && setRemoveTarget(null)}
        title="Remove item?"
        description={`"${removeTarget?.products?.name}" will be removed and stock restored.`}
        onConfirm={() => removeItemMutation.mutate(removeTarget.id)}
        loading={removeItemMutation.isPending}
        confirmLabel="Remove"
      />
    </>
  )
}

// ─── Main Page ────────────────────────────────────────────────────────────────
export default function ServiceJobs() {
  const { role } = useAuthStore()
  const qc = useQueryClient()
  const [newModal,      setNewModal]      = useState(false)
  const [detailJob,     setDetailJob]     = useState(null)
  const [statusFilter,  setStatusFilter]  = useState('all')

  const { data: jobs = [], isLoading } = useQuery({
    queryKey: ['service-jobs'],
    queryFn:  () => api.get('/service-jobs'),
  })
  const { data: customers = [] } = useQuery({ queryKey: ['customers'], queryFn: () => api.get('/customers') })
  const { data: vehicles  = [] } = useQuery({ queryKey: ['vehicles'],  queryFn: () => api.get('/vehicles')  })
  const { data: users     = [] } = useQuery({ queryKey: ['users'],     queryFn: () => api.get('/users')     })
  const { data: products  = [] } = useQuery({ queryKey: ['products'],  queryFn: () => api.get('/products')  })
  const { data: inventory = [] } = useQuery({ queryKey: ['inventory'], queryFn: () => api.get('/inventory') })

  const createMutation = useMutation({
    mutationFn: (data) => api.post('/service-jobs', data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['service-jobs'] })
      setNewModal(false)
      toast({ title: 'Service job created', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const canCreate = ['admin', 'manager', 'technician'].includes(role)
  const filtered  = statusFilter === 'all' ? jobs : jobs.filter((j) => j.status === statusFilter)

  const columns = [
    { key: 'job_number',   label: 'Job #',    render: (r) => <span className="font-medium">{r.job_number ?? r.id?.slice(0, 8)}</span> },
    { key: 'customer',     label: 'Customer', render: (r) => r.customers?.name ?? '—' },
    { key: 'vehicle',      label: 'Vehicle',  render: (r) => r.vehicles?.registration_number ?? '—' },
    { key: 'service_type', label: 'Type',     render: (r) => <span className="capitalize">{r.service_type ?? '—'}</span> },
    { key: 'status',       label: 'Status',   render: (r) => statusBadge(r.status) },
    { key: 'created_at',   label: 'Opened',   render: (r) => formatDate(r.created_at) },
    {
      key: 'actions', label: '', headerClass: 'w-12',
      render: (r) => (
        <Button size="icon" variant="ghost" onClick={() => setDetailJob(r)}>
          <Eye className="h-4 w-4" />
        </Button>
      ),
    },
  ]

  return (
    <div>
      <PageHeader
        title="Service Jobs"
        description="Job cards for vehicle servicing"
        action={canCreate && (
          <Button onClick={() => setNewModal(true)}>
            <Plus className="h-4 w-4 mr-2" /> New Job
          </Button>
        )}
      />

      {isLoading ? (
        <p className="text-muted-foreground text-sm">Loading…</p>
      ) : (
        <DataTable
          columns={columns}
          data={filtered}
          searchPlaceholder="Search by job number, customer or vehicle…"
          searchKeys={['job_number']}
          filterSlot={
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-48"><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Statuses</SelectItem>
                <SelectItem value="estimate">Estimate</SelectItem>
                <SelectItem value="open">Open</SelectItem>
                <SelectItem value="in_progress">In Progress</SelectItem>
                <SelectItem value="waiting_for_parts">Waiting for Parts</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="invoiced">Invoiced</SelectItem>
              </SelectContent>
            </Select>
          }
          emptyMessage="No service jobs found."
        />
      )}

      <NewJobModal
        open={newModal}
        onOpenChange={setNewModal}
        customers={customers}
        vehicles={vehicles}
        users={users}
        onSave={(data) => createMutation.mutate(data)}
        saving={createMutation.isPending}
      />

      {detailJob && (
        <JobDetailModal
          job={detailJob}
          open={!!detailJob}
          onOpenChange={(open) => !open && setDetailJob(null)}
          products={products}
          inventory={inventory}
        />
      )}
    </div>
  )
}
