import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { Plus, Eye, CheckCircle, XCircle } from 'lucide-react'
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
  const map = { pending: 'warning', received: 'success', cancelled: 'muted' }
  return <Badge variant={map[status] ?? 'secondary'}>{status}</Badge>
}

function NewPOModal({ open, onOpenChange, suppliers, products, onSave, saving }) {
  const [supplierId, setSupplierId] = useState('')
  const [notes, setNotes] = useState('')
  const [items, setItems] = useState([{ product_id: '', qty_ordered: 1, unit_cost: '' }])

  function handleOpen(isOpen) {
    if (isOpen) { setSupplierId(''); setNotes(''); setItems([{ product_id: '', qty_ordered: 1, unit_cost: '' }]) }
    onOpenChange(isOpen)
  }

  function setItem(i, k, v) {
    setItems((prev) => prev.map((it, idx) => idx === i ? { ...it, [k]: v } : it))
  }

  function addItem() { setItems((prev) => [...prev, { product_id: '', qty_ordered: 1, unit_cost: '' }]) }
  function removeItem(i) { setItems((prev) => prev.filter((_, idx) => idx !== i)) }

  function handleSubmit(e) {
    e.preventDefault()
    onSave({
      supplier_id: supplierId,
      notes,
      items: items.map((it) => ({ ...it, qty_ordered: Number(it.qty_ordered), unit_cost: Number(it.unit_cost) })),
    })
  }

  const total = items.reduce((s, it) => s + (Number(it.qty_ordered) || 0) * (Number(it.unit_cost) || 0), 0)

  return (
    <Dialog open={open} onOpenChange={handleOpen}>
      <DialogContent className="max-w-2xl">
        <DialogHeader><DialogTitle>New Purchase Order</DialogTitle></DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <div className="col-span-2">
              <Label>Supplier *</Label>
              <Select value={supplierId} onValueChange={setSupplierId} required>
                <SelectTrigger className="mt-1"><SelectValue placeholder="Select supplier…" /></SelectTrigger>
                <SelectContent>
                  {suppliers.filter((s) => s.is_active !== false).map((s) => (
                    <SelectItem key={s.id} value={s.id}>{s.name}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>

          <div>
            <div className="flex items-center justify-between mb-2">
              <Label>Items *</Label>
              <Button type="button" size="sm" variant="outline" onClick={addItem}>+ Add Row</Button>
            </div>
            <div className="space-y-2">
              {items.map((it, i) => (
                <div key={i} className="grid grid-cols-12 gap-2 items-center">
                  <div className="col-span-5">
                    <Select value={it.product_id} onValueChange={(v) => setItem(i, 'product_id', v)}>
                      <SelectTrigger className="h-8 text-xs"><SelectValue placeholder="Product…" /></SelectTrigger>
                      <SelectContent>
                        {products.filter((p) => p.is_active !== false).map((p) => (
                          <SelectItem key={p.id} value={p.id}>{p.name}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="col-span-2">
                    <Input type="number" min="1" placeholder="Qty" value={it.qty_ordered}
                      onChange={(e) => setItem(i, 'qty_ordered', e.target.value)} className="h-8 text-xs" />
                  </div>
                  <div className="col-span-3">
                    <Input type="number" min="0" step="0.01" placeholder="Unit cost" value={it.unit_cost}
                      onChange={(e) => setItem(i, 'unit_cost', e.target.value)} className="h-8 text-xs" />
                  </div>
                  <div className="col-span-2 text-right text-xs text-muted-foreground">
                    {formatCurrency((Number(it.qty_ordered) || 0) * (Number(it.unit_cost) || 0))}
                  </div>
                  {items.length > 1 && (
                    <button type="button" onClick={() => removeItem(i)} className="text-destructive text-xs ml-1">✕</button>
                  )}
                </div>
              ))}
            </div>
            <div className="text-right text-sm font-semibold mt-2">Total: {formatCurrency(total)}</div>
          </div>

          <div>
            <Label>Notes</Label>
            <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={2} className="mt-1" />
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
            <Button type="submit" disabled={saving || !supplierId || items.some((it) => !it.product_id)}>
              {saving ? 'Saving…' : 'Create PO'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}

function PODetailModal({ po, open, onOpenChange, onReceive, onCancel, receiving, cancelling }) {
  if (!po) return null
  const items = po.purchase_order_items ?? po.items ?? []

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-xl">
        <DialogHeader>
          <DialogTitle>PO {po.po_number ?? po.id?.slice(0, 8)}</DialogTitle>
        </DialogHeader>
        <div className="space-y-3 text-sm">
          <div className="grid grid-cols-2 gap-2">
            <div><span className="text-muted-foreground">Supplier:</span> <span className="font-medium">{po.suppliers?.name ?? '—'}</span></div>
            <div><span className="text-muted-foreground">Status:</span> {statusBadge(po.status)}</div>
            <div><span className="text-muted-foreground">Ordered:</span> {formatDate(po.created_at)}</div>
            <div><span className="text-muted-foreground">Total:</span> <span className="font-medium">{formatCurrency(po.total_amount)}</span></div>
          </div>
          {po.notes && <p className="text-muted-foreground italic">{po.notes}</p>}

          <div className="rounded-md border overflow-hidden mt-3">
            <table className="w-full text-xs">
              <thead className="bg-muted/50">
                <tr>
                  <th className="px-3 py-2 text-left font-medium text-muted-foreground">Product</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Qty</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Unit Cost</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                {items.length === 0 ? (
                  <tr><td colSpan={4} className="px-3 py-4 text-center text-muted-foreground">No items.</td></tr>
                ) : items.map((it, i) => (
                  <tr key={it.id ?? i} className="border-t">
                    <td className="px-3 py-2">{it.products?.name ?? it.product_id}</td>
                    <td className="px-3 py-2 text-right">{it.qty_ordered}</td>
                    <td className="px-3 py-2 text-right">{formatCurrency(it.unit_cost)}</td>
                    <td className="px-3 py-2 text-right">{formatCurrency(it.qty_ordered * it.unit_cost)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {po.status === 'pending' && (
            <div className="flex gap-2 pt-2">
              <Button
                className="flex-1 gap-1.5" onClick={onReceive} disabled={receiving}
              >
                <CheckCircle className="h-4 w-4" />
                {receiving ? 'Receiving…' : 'Mark Received'}
              </Button>
              <Button
                variant="destructive" className="flex-1 gap-1.5" onClick={onCancel} disabled={cancelling}
              >
                <XCircle className="h-4 w-4" />
                {cancelling ? 'Cancelling…' : 'Cancel PO'}
              </Button>
            </div>
          )}
        </div>
      </DialogContent>
    </Dialog>
  )
}

export default function Purchases() {
  const { role } = useAuthStore()
  const qc = useQueryClient()
  const [newModal, setNewModal] = useState(false)
  const [detailPO, setDetailPO] = useState(null)
  const [statusFilter, setStatusFilter] = useState('all')

  const { data: purchases = [], isLoading } = useQuery({
    queryKey: ['purchases'],
    queryFn: () => api.get('/purchases'),
  })
  const { data: suppliers = [] } = useQuery({
    queryKey: ['suppliers'],
    queryFn: () => api.get('/suppliers'),
  })
  const { data: products = [] } = useQuery({
    queryKey: ['products'],
    queryFn: () => api.get('/products'),
  })

  const createMutation = useMutation({
    mutationFn: (data) => api.post('/purchases', data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['purchases'] })
      setNewModal(false)
      toast({ title: 'Purchase order created', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const receiveMutation = useMutation({
    mutationFn: (id) => api.post(`/purchases/${id}/receive`),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['purchases'] })
      qc.invalidateQueries({ queryKey: ['inventory'] })
      setDetailPO(null)
      toast({ title: 'PO marked as received — stock updated', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const cancelMutation = useMutation({
    mutationFn: (id) => api.post(`/purchases/${id}/cancel`),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['purchases'] })
      setDetailPO(null)
      toast({ title: 'PO cancelled', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const canCreate = ['admin', 'manager', 'warehouse'].includes(role)

  const filtered = statusFilter === 'all' ? purchases : purchases.filter((p) => p.status === statusFilter)

  const columns = [
    { key: 'po_number', label: 'PO #', render: (r) => <span className="font-medium">{r.po_number ?? r.id?.slice(0, 8)}</span> },
    { key: 'supplier', label: 'Supplier', render: (r) => r.suppliers?.name ?? '—' },
    { key: 'status', label: 'Status', render: (r) => statusBadge(r.status) },
    { key: 'total_amount', label: 'Total', render: (r) => formatCurrency(r.total_amount) },
    { key: 'created_at', label: 'Date', render: (r) => formatDate(r.created_at) },
    {
      key: 'actions', label: '', headerClass: 'w-12',
      render: (r) => (
        <Button size="icon" variant="ghost" onClick={() => setDetailPO(r)}>
          <Eye className="h-4 w-4" />
        </Button>
      ),
    },
  ]

  return (
    <div>
      <PageHeader
        title="Purchase Orders"
        description="Receive stock from suppliers"
        action={canCreate && (
          <Button onClick={() => setNewModal(true)}>
            <Plus className="h-4 w-4 mr-2" /> New PO
          </Button>
        )}
      />

      {isLoading ? (
        <p className="text-muted-foreground text-sm">Loading…</p>
      ) : (
        <DataTable
          columns={columns}
          data={filtered}
          searchPlaceholder="Search by PO number…"
          searchKeys={['po_number']}
          filterSlot={
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-36"><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Statuses</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="received">Received</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
          }
          emptyMessage="No purchase orders found."
        />
      )}

      <NewPOModal
        open={newModal}
        onOpenChange={setNewModal}
        suppliers={suppliers}
        products={products}
        onSave={(data) => createMutation.mutate(data)}
        saving={createMutation.isPending}
      />

      <PODetailModal
        po={detailPO}
        open={!!detailPO}
        onOpenChange={(open) => !open && setDetailPO(null)}
        onReceive={() => receiveMutation.mutate(detailPO.id)}
        onCancel={() => cancelMutation.mutate(detailPO.id)}
        receiving={receiveMutation.isPending}
        cancelling={cancelMutation.isPending}
      />
    </div>
  )
}
