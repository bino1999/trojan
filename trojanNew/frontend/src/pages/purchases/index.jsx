import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { Plus, Eye, CheckCircle, XCircle } from 'lucide-react'
import api from '@/lib/api'
import { formatCurrency, formatDate } from '@/lib/utils'
import { useAuthStore } from '@/store/authStore'
import { toast } from '@/hooks/use-toast'
import PageHeader from '@/components/shared/PageHeader'
import DataTable from '@/components/shared/DataTable'
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
  const today = new Date().toISOString().slice(0, 10)
  const [supplierId, setSupplierId]       = useState('')
  const [orderDate, setOrderDate]         = useState(today)
  const [invoiceNumber, setInvoiceNumber] = useState('')
  const [notes, setNotes]                 = useState('')
  const [items, setItems] = useState([{ product_id: '', qty_ordered: 1, company_price: '', selling_price: '' }])

  function handleOpen(isOpen) {
    if (isOpen) {
      setSupplierId(''); setOrderDate(today); setInvoiceNumber(''); setNotes('')
      setItems([{ product_id: '', qty_ordered: 1, company_price: '', selling_price: '' }])
    }
    onOpenChange(isOpen)
  }

  function setItem(i, k, v) {
    setItems((prev) => prev.map((it, idx) => idx === i ? { ...it, [k]: v } : it))
  }
  function addItem()    { setItems((prev) => [...prev, { product_id: '', qty_ordered: 1, company_price: '', selling_price: '' }]) }
  function removeItem(i){ setItems((prev) => prev.filter((_, idx) => idx !== i)) }

  function handleSubmit(e) {
    e.preventDefault()
    onSave({
      supplier_id:    supplierId,
      order_date:     orderDate,
      invoice_number: invoiceNumber || undefined,
      notes:          notes || undefined,
      items: items.map((it) => ({
        product_id:    it.product_id,
        qty_ordered:   Number(it.qty_ordered),
        company_price: Number(it.company_price),
        selling_price: Number(it.selling_price),
      })),
    })
  }

  const total = items.reduce((s, it) => s + (Number(it.qty_ordered) || 0) * (Number(it.company_price) || 0), 0)
  const canSubmit = supplierId && orderDate && items.every((it) => it.product_id && it.company_price && it.selling_price)

  return (
    <Dialog open={open} onOpenChange={handleOpen}>
      <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
        <DialogHeader><DialogTitle>New Purchase Order</DialogTitle></DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <div>
              <Label>Supplier *</Label>
              <Select value={supplierId} onValueChange={setSupplierId} required>
                <SelectTrigger className="mt-1"><SelectValue placeholder="Select supplier…" /></SelectTrigger>
                <SelectContent>
                  {suppliers.filter((s) => s.is_active !== false).map((s) => (
                    <SelectItem key={s.supplier_id} value={s.supplier_id}>{s.name}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div>
              <Label>Order Date *</Label>
              <Input type="date" value={orderDate} onChange={(e) => setOrderDate(e.target.value)} className="mt-1" required />
            </div>
            <div className="col-span-2">
              <Label>Invoice / Reference Number</Label>
              <Input
                placeholder="e.g. INV-0123 (optional)"
                value={invoiceNumber}
                onChange={(e) => setInvoiceNumber(e.target.value)}
                className="mt-1"
              />
            </div>
          </div>

          <div>
            <div className="flex items-center justify-between mb-2">
              <Label>Items *</Label>
              <Button type="button" size="sm" variant="outline" onClick={addItem}>+ Add Row</Button>
            </div>

            {/* header */}
            <div className="grid grid-cols-12 gap-2 mb-1 px-1 text-xs text-muted-foreground font-medium">
              <span className="col-span-4">Product</span>
              <span className="col-span-2">Qty</span>
              <span className="col-span-2">Cost Price</span>
              <span className="col-span-2">Selling Price</span>
              <span className="col-span-2 text-right">Subtotal</span>
            </div>

            <div className="space-y-2">
              {items.map((it, i) => (
                <div key={i} className="grid grid-cols-12 gap-2 items-center">
                  <div className="col-span-4">
                    <Select value={it.product_id} onValueChange={(v) => setItem(i, 'product_id', v)}>
                      <SelectTrigger className="h-8 text-xs"><SelectValue placeholder="Product…" /></SelectTrigger>
                      <SelectContent>
                        {products.filter((p) => p.is_active !== false).map((p) => (
                          <SelectItem key={p.product_id} value={p.product_id}>{p.name}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="col-span-2">
                    <Input type="number" min="1" placeholder="Qty" value={it.qty_ordered}
                      onChange={(e) => setItem(i, 'qty_ordered', e.target.value)} className="h-8 text-xs" />
                  </div>
                  <div className="col-span-2">
                    <Input type="number" min="0" step="0.01" placeholder="Cost" value={it.company_price}
                      onChange={(e) => setItem(i, 'company_price', e.target.value)} className="h-8 text-xs" />
                  </div>
                  <div className="col-span-2">
                    <Input type="number" min="0" step="0.01" placeholder="Selling" value={it.selling_price}
                      onChange={(e) => setItem(i, 'selling_price', e.target.value)} className="h-8 text-xs" />
                  </div>
                  <div className="col-span-1 text-right text-xs text-muted-foreground">
                    {formatCurrency((Number(it.qty_ordered) || 0) * (Number(it.company_price) || 0))}
                  </div>
                  <div className="col-span-1 flex justify-end">
                    {items.length > 1 && (
                      <button type="button" onClick={() => removeItem(i)}
                        className="text-destructive text-xs hover:opacity-70">✕</button>
                    )}
                  </div>
                </div>
              ))}
            </div>
            <div className="text-right text-sm font-semibold mt-2">
              Total Cost: {formatCurrency(total)}
            </div>
          </div>

          <div>
            <Label>Notes</Label>
            <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={2} className="mt-1" />
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
            <Button type="submit" disabled={saving || !canSubmit}>
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
  const items = po.purchase_order_items ?? []

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-xl">
        <DialogHeader>
          <DialogTitle>
            {po.invoice_number ? `PO — ${po.invoice_number}` : `PO — ${po.purchase_order_id?.slice(0, 8)}…`}
          </DialogTitle>
        </DialogHeader>
        <div className="space-y-3 text-sm">
          <div className="grid grid-cols-2 gap-2">
            <div><span className="text-muted-foreground">Supplier:</span> <span className="font-medium">{po.suppliers?.name ?? '—'}</span></div>
            <div><span className="text-muted-foreground">Status:</span> {statusBadge(po.status)}</div>
            <div><span className="text-muted-foreground">Order Date:</span> {formatDate(po.order_date)}</div>
            <div><span className="text-muted-foreground">Total:</span> <span className="font-medium">{formatCurrency(po.total_amount)}</span></div>
            {po.invoice_number && (
              <div className="col-span-2"><span className="text-muted-foreground">Invoice:</span> {po.invoice_number}</div>
            )}
          </div>
          {po.notes && <p className="text-muted-foreground italic text-xs">{po.notes}</p>}

          <div className="rounded-md border overflow-hidden mt-3">
            <table className="w-full text-xs">
              <thead className="bg-muted/50">
                <tr>
                  <th className="px-3 py-2 text-left font-medium text-muted-foreground">Product</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Qty</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Cost</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Selling</th>
                </tr>
              </thead>
              <tbody>
                {items.length === 0 ? (
                  <tr><td colSpan={4} className="px-3 py-4 text-center text-muted-foreground">No items.</td></tr>
                ) : items.map((it, i) => (
                  <tr key={it.po_item_id ?? i} className="border-t">
                    <td className="px-3 py-2">{it.products?.name ?? it.product_id}</td>
                    <td className="px-3 py-2 text-right">{it.qty_ordered}</td>
                    <td className="px-3 py-2 text-right">{formatCurrency(it.company_price)}</td>
                    <td className="px-3 py-2 text-right">{formatCurrency(it.selling_price)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {po.status === 'pending' && (
            <div className="flex gap-2 pt-2">
              <Button className="flex-1 gap-1.5" onClick={onReceive} disabled={receiving || items.length === 0}>
                <CheckCircle className="h-4 w-4" />
                {receiving ? 'Receiving…' : 'Mark Received'}
              </Button>
              <Button variant="destructive" className="flex-1 gap-1.5" onClick={onCancel} disabled={cancelling}>
                <XCircle className="h-4 w-4" />
                {cancelling ? 'Cancelling…' : 'Cancel PO'}
              </Button>
            </div>
          )}
          {po.status === 'pending' && items.length === 0 && (
            <p className="text-xs text-muted-foreground text-center">Loading items…</p>
          )}
        </div>
      </DialogContent>
    </Dialog>
  )
}

export default function Purchases() {
  const { role } = useAuthStore()
  const qc = useQueryClient()
  const [newModal, setNewModal]   = useState(false)
  const [detailPO, setDetailPO]   = useState(null)
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

  // Fetch full PO detail (with items) when a row is clicked
  const { data: poDetail } = useQuery({
    queryKey: ['purchase', detailPO?.purchase_order_id],
    queryFn: () => api.get(`/purchases/${detailPO.purchase_order_id}`),
    enabled: !!detailPO?.purchase_order_id,
  })

  const createMutation = useMutation({
    mutationFn: (data) => api.post('/purchases', data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['purchases'] })
      setNewModal(false)
      toast({ title: 'Purchase order created', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err?.error ?? err?.message ?? 'Failed', variant: 'destructive' }),
  })

  const receiveMutation = useMutation({
    mutationFn: ({ id, body }) => api.post(`/purchases/${id}/receive`, body),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['purchases'] })
      qc.invalidateQueries({ queryKey: ['inventory'] })
      setDetailPO(null)
      toast({ title: 'PO received — inventory updated', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err?.error ?? err?.message ?? 'Failed', variant: 'destructive' }),
  })

  const cancelMutation = useMutation({
    mutationFn: (id) => api.post(`/purchases/${id}/cancel`),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['purchases'] })
      setDetailPO(null)
      toast({ title: 'PO cancelled', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err?.error ?? err?.message ?? 'Failed', variant: 'destructive' }),
  })

  const canCreate = ['admin', 'manager', 'warehouse'].includes(role)
  const filtered  = statusFilter === 'all' ? purchases : purchases.filter((p) => p.status === statusFilter)

  // Use full detail (with items) for the modal when available
  const modalPO = poDetail ?? detailPO

  function handleReceive() {
    if (!modalPO) return
    const items = modalPO.purchase_order_items ?? []
    const body = {
      received_date: new Date().toISOString().slice(0, 10),
      items: items.map((item) => ({
        po_item_id:    item.po_item_id,
        product_id:    item.product_id,
        qty_received:  item.qty_ordered,
        supplier_id:   modalPO.supplier_id,
        company_price: item.company_price,
        selling_price: item.selling_price,
      })),
    }
    receiveMutation.mutate({ id: modalPO.purchase_order_id, body })
  }

  const columns = [
    {
      key: 'po_ref', label: 'PO Ref',
      render: (r) => <span className="font-medium font-mono text-xs">{r.invoice_number ?? r.purchase_order_id?.slice(0, 8)}</span>,
    },
    { key: 'supplier', label: 'Supplier', render: (r) => r.suppliers?.name ?? '—' },
    { key: 'order_date', label: 'Date', render: (r) => formatDate(r.order_date) },
    { key: 'status', label: 'Status', render: (r) => statusBadge(r.status) },
    { key: 'total_amount', label: 'Total', render: (r) => formatCurrency(r.total_amount) },
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
        description="Receive stock from suppliers — mark PO received to update inventory"
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
          searchPlaceholder="Search by invoice number…"
          searchKeys={['invoice_number']}
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
        po={modalPO}
        open={!!detailPO}
        onOpenChange={(open) => { if (!open) { setDetailPO(null) } }}
        onReceive={handleReceive}
        onCancel={() => cancelMutation.mutate(modalPO?.purchase_order_id)}
        receiving={receiveMutation.isPending}
        cancelling={cancelMutation.isPending}
      />
    </div>
  )
}
