import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { Plus, Eye } from 'lucide-react'
import api from '@/lib/api'
import { formatCurrency, formatDate } from '@/lib/utils'
import { useAuthStore } from '@/store/authStore'
import { toast } from '@/hooks/use-toast'
import PageHeader from '@/components/shared/PageHeader'
import DataTable from '@/components/shared/DataTable'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select'

function NewSaleModal({ open, onOpenChange, customers, products, inventory, onSave, saving }) {
  const [customerId, setCustomerId] = useState('none')
  const [paymentMethod, setPaymentMethod] = useState('cash')
  const [notes, setNotes] = useState('')
  const [items, setItems] = useState([{ product_id: '', qty_sold: 1 }])

  function handleOpen(isOpen) {
    if (isOpen) { setCustomerId('none'); setPaymentMethod('cash'); setNotes(''); setItems([{ product_id: '', qty_sold: 1 }]) }
    onOpenChange(isOpen)
  }

  function setItem(i, k, v) {
    setItems((prev) => prev.map((it, idx) => idx === i ? { ...it, [k]: v } : it))
  }

  function addRow() { setItems((prev) => [...prev, { product_id: '', qty_sold: 1 }]) }
  function removeRow(i) { setItems((prev) => prev.filter((_, idx) => idx !== i)) }

  function getPrice(productId) {
    const inv = inventory.find((i) => i.product_id === productId)
    return inv?.selling_price ?? 0
  }

  const total = items.reduce((s, it) => s + (Number(it.qty_sold) || 0) * getPrice(it.product_id), 0)

  function handleSubmit(e) {
    e.preventDefault()
    onSave({
      customer_id: customerId === 'none' ? null : customerId,
      payment_method: paymentMethod,
      notes,
      sale_date: new Date().toISOString().split('T')[0],
      items: items.map((it) => {
        const inv = inventory.find((i) => i.product_id === it.product_id)
        return {
          product_id: it.product_id,
          qty_sold: Number(it.qty_sold),
          inventory_id: inv?.inventory_id ?? inv?.id,
          unit_price: inv?.selling_price ?? 0,
        }
      }),
    })
  }

  return (
    <Dialog open={open} onOpenChange={handleOpen}>
      <DialogContent className="max-w-2xl">
        <DialogHeader><DialogTitle>New Sale</DialogTitle></DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <Label>Customer (optional)</Label>
            <Select value={customerId} onValueChange={setCustomerId}>
              <SelectTrigger className="mt-1"><SelectValue placeholder="Walk-in / no customer" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="none">Walk-in</SelectItem>
                {customers.map((c) => <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>)}
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label>Payment Method *</Label>
            <Select value={paymentMethod} onValueChange={setPaymentMethod}>
              <SelectTrigger className="mt-1"><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="cash">Cash</SelectItem>
                <SelectItem value="card">Card</SelectItem>
                <SelectItem value="bank_transfer">Bank Transfer</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <div className="flex items-center justify-between mb-2">
              <Label>Items *</Label>
              <Button type="button" size="sm" variant="outline" onClick={addRow}>+ Add Row</Button>
            </div>
            <div className="space-y-2">
              {items.map((it, i) => {
                const price = getPrice(it.product_id)
                return (
                  <div key={i} className="grid grid-cols-12 gap-2 items-center">
                    <div className="col-span-6">
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
                      <Input type="number" min="1" placeholder="Qty" value={it.qty_sold}
                        onChange={(e) => setItem(i, 'qty_sold', e.target.value)} className="h-8 text-xs" />
                    </div>
                    <div className="col-span-2 text-xs text-muted-foreground text-right">
                      @ {formatCurrency(price)}
                    </div>
                    <div className="col-span-1 text-xs font-medium text-right">
                      {formatCurrency((Number(it.qty_sold) || 0) * price)}
                    </div>
                    {items.length > 1 && (
                      <button type="button" onClick={() => removeRow(i)} className="text-destructive text-xs">✕</button>
                    )}
                  </div>
                )
              })}
            </div>
            <div className="text-right text-sm font-semibold mt-2 border-t pt-2">
              Total: {formatCurrency(total)}
            </div>
          </div>

          <div>
            <Label>Notes</Label>
            <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={2} className="mt-1" />
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
            <Button type="submit" disabled={saving || items.some((it) => !it.product_id)}>
              {saving ? 'Processing…' : 'Confirm Sale'}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}

function SaleDetailModal({ sale, open, onOpenChange }) {
  const { data: detail } = useQuery({
    queryKey: ['sale', sale?.id],
    queryFn: () => api.get(`/sales/${sale.id}`),
    enabled: !!sale?.id,
  })
  const s = detail ?? sale
  const items = s?.sale_items ?? s?.items ?? []

  if (!sale) return null
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-lg">
        <DialogHeader>
          <DialogTitle>Sale {s?.sale_number ?? s?.id?.slice(0, 8)}</DialogTitle>
        </DialogHeader>
        <div className="space-y-3 text-sm">
          <div className="grid grid-cols-2 gap-2">
            <div><span className="text-muted-foreground">Customer:</span> <span className="font-medium">{s?.customers?.name ?? 'Walk-in'}</span></div>
            <div><span className="text-muted-foreground">Date:</span> {formatDate(s?.created_at)}</div>
            <div><span className="text-muted-foreground">Total:</span> <span className="font-semibold">{formatCurrency(s?.total_amount)}</span></div>
            <div><span className="text-muted-foreground">Payment:</span> <span className="font-medium capitalize">{s?.payment_method?.replace('_', ' ') ?? '—'}</span></div>
          </div>
          {s?.notes && <p className="text-muted-foreground italic">{s.notes}</p>}
          <div className="rounded-md border overflow-hidden">
            <table className="w-full text-xs">
              <thead className="bg-muted/50">
                <tr>
                  <th className="px-3 py-2 text-left font-medium text-muted-foreground">Product</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Qty</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Price</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                {items.map((it, i) => (
                  <tr key={it.id ?? i} className="border-t">
                    <td className="px-3 py-2">{it.products?.name ?? it.product_id}</td>
                    <td className="px-3 py-2 text-right">{it.qty_sold}</td>
                    <td className="px-3 py-2 text-right">{formatCurrency(it.unit_price)}</td>
                    <td className="px-3 py-2 text-right">{formatCurrency(it.qty_sold * it.unit_price)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  )
}

export default function Sales() {
  const { role } = useAuthStore()
  const qc = useQueryClient()
  const [newModal, setNewModal] = useState(false)
  const [detailSale, setDetailSale] = useState(null)

  const { data: sales = [], isLoading } = useQuery({ queryKey: ['sales'], queryFn: () => api.get('/sales') })
  const { data: customers = [] } = useQuery({ queryKey: ['customers'], queryFn: () => api.get('/customers') })
  const { data: products = [] } = useQuery({ queryKey: ['products'], queryFn: () => api.get('/products') })
  const { data: inventory = [] } = useQuery({ queryKey: ['inventory'], queryFn: () => api.get('/inventory') })

  const createMutation = useMutation({
    mutationFn: (data) => api.post('/sales', data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['sales'] })
      qc.invalidateQueries({ queryKey: ['inventory'] })
      setNewModal(false)
      toast({ title: 'Sale recorded', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const canCreate = ['admin', 'manager', 'cashier'].includes(role)

  const columns = [
    { key: 'sale_number', label: 'Sale #', render: (r) => <span className="font-medium">{r.sale_number ?? r.id?.slice(0, 8)}</span> },
    { key: 'customer', label: 'Customer', render: (r) => r.customers?.name ?? 'Walk-in' },
    { key: 'total_amount', label: 'Total', render: (r) => formatCurrency(r.total_amount) },
    { key: 'created_at', label: 'Date', render: (r) => formatDate(r.created_at) },
    {
      key: 'actions', label: '', headerClass: 'w-12',
      render: (r) => <Button size="icon" variant="ghost" onClick={() => setDetailSale(r)}><Eye className="h-4 w-4" /></Button>,
    },
  ]

  return (
    <div>
      <PageHeader
        title="Direct Sales"
        description="Walk-in counter sales"
        action={canCreate && (
          <Button onClick={() => setNewModal(true)}>
            <Plus className="h-4 w-4 mr-2" /> New Sale
          </Button>
        )}
      />
      {isLoading ? (
        <p className="text-muted-foreground text-sm">Loading…</p>
      ) : (
        <DataTable columns={columns} data={sales} searchPlaceholder="Search by sale number…" searchKeys={['sale_number']} emptyMessage="No sales yet." />
      )}
      <NewSaleModal open={newModal} onOpenChange={setNewModal} customers={customers} products={products} inventory={inventory} onSave={(d) => createMutation.mutate(d)} saving={createMutation.isPending} />
      <SaleDetailModal sale={detailSale} open={!!detailSale} onOpenChange={(open) => !open && setDetailSale(null)} />
    </div>
  )
}
