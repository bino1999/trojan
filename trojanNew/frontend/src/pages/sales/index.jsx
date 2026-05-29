import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { Plus, Eye, Search, Check, CreditCard, Banknote, DollarSign, Receipt, Printer } from 'lucide-react'
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

const PAYMENT_METHOD_LABELS = {
  cash: 'Cash',
  card: 'Card',
  bank_transfer: 'Bank Transfer',
  cheque: 'Cheque',
}

// ─── StockBadge ────────────────────────────────────────────────────────────────

function StockBadge({ qty, reorderLevel }) {
  if (qty === 0)
    return <span className="text-xs px-2 py-0.5 rounded-full font-medium bg-destructive/10 text-destructive">Out of stock</span>
  if (reorderLevel && qty <= reorderLevel)
    return <span className="text-xs px-2 py-0.5 rounded-full font-medium bg-orange-100 text-orange-700">Low · {qty}</span>
  return <span className="text-xs px-2 py-0.5 rounded-full font-medium bg-green-100 text-green-700">{qty} in stock</span>
}

// ─── AddItemModal ───────────────────────────────────────────────────────────────

function AddItemModal({ open, onOpenChange, inventory, onAdd }) {
  const [selectedId, setSelectedId] = useState('')
  const [qty, setQty]               = useState(1)
  const [search, setSearch]         = useState('')

  function handleOpen(isOpen) {
    if (isOpen) { setSelectedId(''); setQty(1); setSearch('') }
    onOpenChange(isOpen)
  }

  const sorted = [...inventory].sort((a, b) => {
    const na = a.products?.name ?? ''
    const nb = b.products?.name ?? ''
    if (na !== nb) return na.localeCompare(nb)
    return (a.suppliers?.name ?? '').localeCompare(b.suppliers?.name ?? '')
  })

  const filtered = sorted.filter((item) => {
    const q = search.toLowerCase()
    if (!q) return true
    return (
      item.products?.name?.toLowerCase().includes(q) ||
      item.suppliers?.name?.toLowerCase().includes(q)
    )
  })

  const selected     = inventory.find((i) => i.id === selectedId)
  const currentStock = selected?.qty_in_stock ?? null
  const outOfStock   = currentStock !== null && currentStock === 0
  const overQty      = currentStock !== null && Number(qty) > currentStock && !outOfStock
  const subtotal     = selected && Number(qty) > 0 ? Number(qty) * (selected.selling_price ?? 0) : null

  function handleSubmit(e) {
    e.preventDefault()
    if (!selected) return
    onAdd({
      inventory_id:  selected.id,
      product_id:    selected.product_id,
      qty_sold:      Number(qty),
      unit_price:    selected.selling_price,
      product_name:  selected.products?.name ?? '',
      supplier_name: selected.suppliers?.name ?? '',
    })
    handleOpen(false)
  }

  return (
    <Dialog open={open} onOpenChange={handleOpen}>
      <DialogContent className="max-w-xl max-h-[85vh] flex flex-col">
        <DialogHeader><DialogTitle>Add Item to Sale</DialogTitle></DialogHeader>

        {/* Search */}
        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Search by product or supplier…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-9"
          />
        </div>

        {/* Inventory list */}
        <div className="flex-1 overflow-y-auto space-y-2 min-h-0 pr-1">
          {filtered.length === 0 && (
            <p className="text-center text-muted-foreground text-sm py-8">No items match your search.</p>
          )}
          {filtered.map((item) => {
            const isSelected = item.id === selectedId
            const disabled   = item.qty_in_stock === 0
            return (
              <button
                key={item.id}
                type="button"
                disabled={disabled}
                onClick={() => { setSelectedId(item.id); setQty(1) }}
                className={[
                  'w-full text-left rounded-md border px-3 py-2.5 transition-colors',
                  isSelected
                    ? 'border-primary bg-primary/5'
                    : disabled
                    ? 'opacity-40 cursor-not-allowed border-border'
                    : 'border-border hover:bg-muted/50 cursor-pointer',
                ].join(' ')}
              >
                <div className="flex items-center justify-between gap-3">
                  <div className="min-w-0">
                    <p className="font-medium text-sm truncate">{item.products?.name ?? '—'}</p>
                    <p className="text-xs text-muted-foreground truncate">{item.suppliers?.name ?? 'Unknown supplier'}</p>
                  </div>
                  <div className="flex items-center gap-2 shrink-0">
                    <div className="text-right">
                      <p className="text-sm font-semibold">{formatCurrency(item.selling_price)}</p>
                      <p className="text-xs text-muted-foreground">per unit</p>
                    </div>
                    <StockBadge qty={item.qty_in_stock} reorderLevel={item.reorder_level} />
                    {isSelected && <Check className="h-4 w-4 text-primary shrink-0" />}
                  </div>
                </div>

                {/* Selected item extra details */}
                {isSelected && (
                  <div className="mt-2 pt-2 border-t flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                    {item.batch_number && <span>Batch: {item.batch_number}</span>}
                    {item.expiry_date  && <span>Expires: {formatDate(item.expiry_date)}</span>}
                    {(item.rack_no || item.bin_no) && <span>Location: {[item.rack_no && `Rack ${item.rack_no}`, item.bin_no && `Bin ${item.bin_no}`].filter(Boolean).join(' · ')}</span>}
                    {item.is_genuine === false && <span className="text-amber-600">⚠ Non-genuine part</span>}
                  </div>
                )}
              </button>
            )
          })}
        </div>

        {/* Qty + confirm */}
        {selected && (
          <form onSubmit={handleSubmit} className="border-t pt-3 space-y-3">
            <div className="flex items-center gap-3">
              <Label className="shrink-0">Quantity</Label>
              <Input
                type="number"
                min={1}
                max={currentStock ?? undefined}
                value={qty}
                onChange={(e) => setQty(e.target.value)}
                className="w-24"
              />
              {overQty && (
                <p className="text-xs text-destructive">Exceeds available stock ({currentStock})</p>
              )}
              {subtotal !== null && !overQty && (
                <p className="ml-auto text-sm font-semibold">Subtotal: {formatCurrency(subtotal)}</p>
              )}
            </div>
            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => handleOpen(false)}>Cancel</Button>
              <Button type="submit" disabled={outOfStock || overQty || Number(qty) < 1}>
                Add Item
              </Button>
            </DialogFooter>
          </form>
        )}
        {!selected && (
          <div className="border-t pt-3">
            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => handleOpen(false)}>Cancel</Button>
            </DialogFooter>
          </div>
        )}
      </DialogContent>
    </Dialog>
  )
}

// ─── NewSaleModal ───────────────────────────────────────────────────────────────

function NewSaleModal({ open, onOpenChange, customers, inventory, items, setItems, onProceed }) {
  const [customerId,  setCustomerId]  = useState('none')
  const [notes,       setNotes]       = useState('')
  const [addItemOpen, setAddItemOpen] = useState(false)

  function handleOpen(isOpen) {
    if (isOpen) { setCustomerId('none'); setNotes(''); setItems([]) }
    onOpenChange(isOpen)
  }

  function addItem(item) {
    setItems((prev) => {
      const idx = prev.findIndex((i) => i.inventory_id === item.inventory_id)
      if (idx >= 0) {
        const updated = [...prev]
        updated[idx] = { ...updated[idx], qty_sold: updated[idx].qty_sold + item.qty_sold }
        return updated
      }
      return [...prev, item]
    })
  }

  function removeItem(idx) {
    setItems((prev) => prev.filter((_, i) => i !== idx))
  }

  const total = items.reduce((s, it) => s + it.qty_sold * it.unit_price, 0)

  function handleProceed(e) {
    e.preventDefault()
    onProceed({ customer_id: customerId === 'none' ? null : customerId, notes, items })
    onOpenChange(false)
  }

  return (
    <>
      <Dialog open={open} onOpenChange={handleOpen}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader><DialogTitle>New Sale</DialogTitle></DialogHeader>
          <form onSubmit={handleProceed} className="space-y-4">

            {/* Customer */}
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

            {/* Items */}
            <div>
              <div className="flex items-center justify-between mb-2">
                <Label>Items *</Label>
                <Button type="button" size="sm" variant="outline" onClick={() => setAddItemOpen(true)}>
                  <Plus className="h-3.5 w-3.5 mr-1" /> Add Item
                </Button>
              </div>

              {items.length === 0 ? (
                <div className="rounded-md border border-dashed py-8 text-center text-sm text-muted-foreground">
                  No items added yet — click "Add Item" to select from inventory.
                </div>
              ) : (
                <div className="rounded-md border overflow-hidden">
                  <table className="w-full text-xs">
                    <thead className="bg-muted/50">
                      <tr>
                        <th className="px-3 py-2 text-left font-medium text-muted-foreground">Product</th>
                        <th className="px-3 py-2 text-left font-medium text-muted-foreground">Supplier</th>
                        <th className="px-3 py-2 text-right font-medium text-muted-foreground">Qty</th>
                        <th className="px-3 py-2 text-right font-medium text-muted-foreground">Unit Price</th>
                        <th className="px-3 py-2 text-right font-medium text-muted-foreground">Subtotal</th>
                        <th className="px-3 py-2 w-8" />
                      </tr>
                    </thead>
                    <tbody>
                      {items.map((it, i) => (
                        <tr key={i} className="border-t">
                          <td className="px-3 py-2 font-medium">{it.product_name}</td>
                          <td className="px-3 py-2 text-muted-foreground">{it.supplier_name || '—'}</td>
                          <td className="px-3 py-2 text-right">{it.qty_sold}</td>
                          <td className="px-3 py-2 text-right">{formatCurrency(it.unit_price)}</td>
                          <td className="px-3 py-2 text-right font-medium">{formatCurrency(it.qty_sold * it.unit_price)}</td>
                          <td className="px-3 py-2">
                            <button type="button" onClick={() => removeItem(i)} className="text-destructive hover:opacity-70 text-xs">✕</button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                  <div className="flex justify-end px-3 py-2 border-t bg-muted/30 font-semibold text-sm">
                    Total: {formatCurrency(total)}
                  </div>
                </div>
              )}
            </div>

            {/* Notes */}
            <div>
              <Label>Notes</Label>
              <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={2} className="mt-1" />
            </div>

            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
              <Button type="submit" disabled={items.length === 0}>
                Proceed to Payment →
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <AddItemModal
        open={addItemOpen}
        onOpenChange={setAddItemOpen}
        inventory={inventory}
        onAdd={addItem}
      />
    </>
  )
}

// ─── PaymentModal ───────────────────────────────────────────────────────────────

function PaymentModal({ open, onOpenChange, pendingSale, onConfirm, saving, onBack }) {
  const [method,    setMethod]    = useState('cash')
  const [reference, setReference] = useState('')
  const [tendered,  setTendered]  = useState('')

  function handleOpen(v) {
    if (v) { setMethod('cash'); setReference(''); setTendered('') }
    onOpenChange(v)
  }

  const items    = pendingSale?.items ?? []
  const total    = items.reduce((s, it) => s + it.qty_sold * it.unit_price, 0)
  const change   = method === 'cash' ? (Number(tendered) || 0) - total : 0
  const cashShort = method === 'cash' && tendered !== '' && Number(tendered) < total
  const canConfirm = !!method && !(method === 'cash' && cashShort)

  function handleConfirm() {
    onConfirm({
      ...pendingSale,
      payment_method:    method,
      payment_reference: reference || null,
      amount_tendered:   method === 'cash' ? Number(tendered) : null,
      sale_date:         new Date().toISOString().slice(0, 10),
    })
  }

  const methodButtons = [
    { value: 'cash',          label: 'Cash',          Icon: Banknote   },
    { value: 'card',          label: 'Card',          Icon: CreditCard },
    { value: 'bank_transfer', label: 'Bank Transfer', Icon: DollarSign },
    { value: 'cheque',        label: 'Cheque',        Icon: Receipt    },
  ]

  return (
    <Dialog open={open} onOpenChange={handleOpen}>
      <DialogContent className="max-w-md">
        <DialogHeader><DialogTitle>Collect Payment</DialogTitle></DialogHeader>
        <div className="space-y-4 text-sm">

          {/* Order summary */}
          <div className="rounded-md border divide-y text-sm">
            {items.map((it, i) => (
              <div key={i} className="flex justify-between px-3 py-2 text-muted-foreground">
                <span>{it.product_name} × {it.qty_sold}</span>
                <span>{formatCurrency(it.qty_sold * it.unit_price)}</span>
              </div>
            ))}
            <div className="flex justify-between px-3 py-2 font-semibold">
              <span>Total Due</span>
              <span>{formatCurrency(total)}</span>
            </div>
          </div>

          {/* Payment method grid */}
          <div className="space-y-1.5">
            <Label>Payment Method</Label>
            <div className="grid grid-cols-2 gap-2">
              {methodButtons.map(({ value, label, Icon }) => (
                <button
                  key={value}
                  type="button"
                  onClick={() => { setMethod(value); setReference(''); setTendered('') }}
                  className={[
                    'flex items-center gap-2 px-3 py-2.5 rounded-md border text-sm transition-colors',
                    method === value
                      ? 'border-primary bg-primary/10 text-primary font-medium'
                      : 'border-border hover:bg-muted',
                  ].join(' ')}
                >
                  <Icon className="h-4 w-4" /> {label}
                </button>
              ))}
            </div>
          </div>

          {/* Method-specific fields */}
          {method === 'cash' && (
            <div className="space-y-3">
              <div className="space-y-1.5">
                <Label>Amount Tendered</Label>
                <Input
                  type="number"
                  min={0}
                  step="0.01"
                  placeholder="0.00"
                  value={tendered}
                  onChange={(e) => setTendered(e.target.value)}
                />
              </div>
              {tendered !== '' && Number(tendered) > 0 && (
                <div className={[
                  'flex justify-between px-3 py-2 rounded-md font-semibold',
                  change >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700',
                ].join(' ')}>
                  <span>{change >= 0 ? 'Change Due' : 'Amount Short'}</span>
                  <span>{formatCurrency(Math.abs(change))}</span>
                </div>
              )}
            </div>
          )}

          {method === 'card' && (
            <div className="space-y-1.5">
              <Label>Card Reference / Last 4 Digits <span className="text-muted-foreground">(optional)</span></Label>
              <Input placeholder="e.g. 4242" value={reference} onChange={(e) => setReference(e.target.value)} />
            </div>
          )}

          {method === 'bank_transfer' && (
            <div className="space-y-1.5">
              <Label>Transaction Reference <span className="text-muted-foreground">(optional)</span></Label>
              <Input placeholder="e.g. TXN-123456" value={reference} onChange={(e) => setReference(e.target.value)} />
            </div>
          )}

          {method === 'cheque' && (
            <div className="space-y-1.5">
              <Label>Cheque Number <span className="text-muted-foreground">(optional)</span></Label>
              <Input placeholder="e.g. 001234" value={reference} onChange={(e) => setReference(e.target.value)} />
            </div>
          )}
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={onBack}>← Back</Button>
          <Button onClick={handleConfirm} disabled={!canConfirm || saving}>
            {saving ? 'Processing…' : 'Confirm Sale'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

// ─── ReceiptModal ───────────────────────────────────────────────────────────────

function ReceiptModal({ open, onOpenChange, saleId }) {
  const { data: sale } = useQuery({
    queryKey: ['sale', saleId],
    queryFn: () => api.get(`/sales/${saleId}`),
    enabled: !!saleId && open,
  })

  const items = sale?.sale_items ?? []
  const total = sale?.total_amount ?? 0

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-md">
        <DialogHeader>
          <DialogTitle>Receipt — {sale?.invoice_number ?? sale?.sale_number ?? '…'}</DialogTitle>
        </DialogHeader>

        {!sale ? (
          <p className="text-center text-muted-foreground text-sm py-8">Loading receipt…</p>
        ) : (
          <div id="receipt-printable" className="space-y-3 text-sm font-mono">
            {/* Header */}
            <div className="text-center border-b pb-3">
              <p className="font-bold text-base">VEHICLE SERVICE CENTER</p>
              {sale.invoice_number && (
                <p className="text-xs text-muted-foreground">Invoice: {sale.invoice_number}</p>
              )}
              <p className="text-xs text-muted-foreground">Sale #: {sale.sale_number}</p>
              <p className="text-xs text-muted-foreground">
                Date: {formatDate(sale.payment_date ?? sale.created_at)}
              </p>
            </div>

            {/* Customer */}
            <div className="text-xs space-y-0.5">
              <div className="flex gap-2">
                <span className="text-muted-foreground w-20">Customer:</span>
                <span className="font-medium">{sale.customers?.name ?? 'Walk-in Customer'}</span>
              </div>
            </div>

            {/* Items */}
            <div className="border-t pt-2">
              <table className="w-full text-xs">
                <thead>
                  <tr className="border-b">
                    <th className="text-left py-0.5 font-medium">Item</th>
                    <th className="text-right py-0.5 font-medium">Qty</th>
                    <th className="text-right py-0.5 font-medium">Price</th>
                    <th className="text-right py-0.5 font-medium">Total</th>
                  </tr>
                </thead>
                <tbody>
                  {items.map((it, i) => (
                    <tr key={it.id ?? i} className="border-b border-dashed">
                      <td className="py-0.5">{it.products?.name ?? '—'}</td>
                      <td className="py-0.5 text-right">{it.qty_sold}</td>
                      <td className="py-0.5 text-right">{formatCurrency(it.unit_price)}</td>
                      <td className="py-0.5 text-right">{formatCurrency(it.qty_sold * it.unit_price)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Total */}
            <div className="border-t">
              <div className="flex justify-between py-1 font-bold text-sm">
                <span>TOTAL</span>
                <span>{formatCurrency(total)}</span>
              </div>
            </div>

            {/* Payment details */}
            <div className="border-t pt-2 text-xs space-y-0.5">
              <div className="flex gap-2">
                <span className="text-muted-foreground w-24">Payment:</span>
                <span className="capitalize">{PAYMENT_METHOD_LABELS[sale.payment_method] ?? sale.payment_method ?? '—'}</span>
              </div>
              {sale.payment_method === 'cash' && sale.amount_tendered != null && (
                <>
                  <div className="flex gap-2">
                    <span className="text-muted-foreground w-24">Tendered:</span>
                    <span>{formatCurrency(sale.amount_tendered)}</span>
                  </div>
                  <div className="flex gap-2">
                    <span className="text-muted-foreground w-24">Change:</span>
                    <span>{formatCurrency(Math.max(0, Number(sale.amount_tendered) - total))}</span>
                  </div>
                </>
              )}
              {sale.payment_reference && (
                <div className="flex gap-2">
                  <span className="text-muted-foreground w-24">Reference:</span>
                  <span>{sale.payment_reference}</span>
                </div>
              )}
            </div>

            <div className="border-t pt-3 text-center text-xs text-muted-foreground">
              <p>Thank you for your business!</p>
            </div>
          </div>
        )}

        <DialogFooter>
          <Button variant="outline" onClick={() => onOpenChange(false)}>Close</Button>
          <Button onClick={() => window.print()} className="gap-1.5" disabled={!sale}>
            <Printer className="h-4 w-4" /> Print Receipt
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

// ─── SaleDetailModal ────────────────────────────────────────────────────────────

function SaleDetailModal({ sale, open, onOpenChange }) {
  const { data: detail } = useQuery({
    queryKey: ['sale', sale?.id],
    queryFn: () => api.get(`/sales/${sale.id}`),
    enabled: !!sale?.id,
  })
  const s     = detail ?? sale
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
            <div><span className="text-muted-foreground">Invoice #:</span> <span className="font-medium">{s?.invoice_number ?? '—'}</span></div>
            <div><span className="text-muted-foreground">Date:</span> {formatDate(s?.created_at)}</div>
            <div><span className="text-muted-foreground">Customer:</span> <span className="font-medium">{s?.customers?.name ?? 'Walk-in'}</span></div>
            <div><span className="text-muted-foreground">Payment:</span> <span className="font-medium capitalize">{PAYMENT_METHOD_LABELS[s?.payment_method] ?? s?.payment_method?.replace('_', ' ') ?? '—'}</span></div>
            <div><span className="text-muted-foreground">Total:</span> <span className="font-semibold">{formatCurrency(s?.total_amount)}</span></div>
            {s?.payment_reference && (
              <div><span className="text-muted-foreground">Reference:</span> <span className="font-medium">{s.payment_reference}</span></div>
            )}
            {s?.payment_method === 'cash' && s?.amount_tendered != null && (
              <div><span className="text-muted-foreground">Change:</span> <span className="font-medium">{formatCurrency(Math.max(0, Number(s.amount_tendered) - Number(s.total_amount)))}</span></div>
            )}
          </div>
          {s?.notes && <p className="text-muted-foreground italic">{s.notes}</p>}
          <div className="rounded-md border overflow-hidden">
            <table className="w-full text-xs">
              <thead className="bg-muted/50">
                <tr>
                  <th className="px-3 py-2 text-left font-medium text-muted-foreground">Product</th>
                  <th className="px-3 py-2 text-left font-medium text-muted-foreground">Supplier</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Qty</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Price</th>
                  <th className="px-3 py-2 text-right font-medium text-muted-foreground">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                {items.map((it, i) => (
                  <tr key={it.id ?? i} className="border-t">
                    <td className="px-3 py-2">{it.products?.name ?? it.product_id}</td>
                    <td className="px-3 py-2 text-muted-foreground">{it.supplier_name ?? '—'}</td>
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

// ─── Sales page ─────────────────────────────────────────────────────────────────

export default function Sales() {
  const { role } = useAuthStore()
  const qc = useQueryClient()

  const [newModal,         setNewModal]         = useState(false)
  const [paymentModal,     setPaymentModal]     = useState(false)
  const [receiptModal,     setReceiptModal]     = useState(false)
  const [pendingSale,      setPendingSale]      = useState(null)
  const [completedSaleId,  setCompletedSaleId]  = useState(null)
  const [pendingItems,     setPendingItems]     = useState([])
  const [detailSale,       setDetailSale]       = useState(null)

  const { data: sales = [], isLoading } = useQuery({ queryKey: ['sales'],     queryFn: () => api.get('/sales') })
  const { data: customers = [] }        = useQuery({ queryKey: ['customers'], queryFn: () => api.get('/customers') })
  const { data: inventory = [] }        = useQuery({ queryKey: ['inventory'], queryFn: () => api.get('/inventory') })

  const createMutation = useMutation({
    mutationFn: (data) => api.post('/sales', data),
    onSuccess: (created) => {
      qc.invalidateQueries({ queryKey: ['sales'] })
      qc.invalidateQueries({ queryKey: ['inventory'] })
      setPaymentModal(false)
      setCompletedSaleId(created.sale_id ?? created.id)
      setReceiptModal(true)
      setPendingSale(null)
      setPendingItems([])
      toast({ title: 'Sale recorded', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  function handleProceed(saleData) {
    setPendingSale(saleData)
    setPaymentModal(true)
  }

  function handleBack() {
    setPaymentModal(false)
    setNewModal(true)
  }

  function handleConfirmPayment(paymentData) {
    createMutation.mutate(paymentData)
  }

  const canCreate = ['admin', 'manager', 'cashier'].includes(role)

  const columns = [
    {
      key: 'invoice_number', label: 'Invoice #',
      render: (r) => <span className="font-medium text-xs">{r.invoice_number ?? '—'}</span>,
    },
    {
      key: 'sale_number', label: 'Sale #',
      render: (r) => <span className="font-medium">{r.sale_number ?? r.id?.slice(0, 8)}</span>,
    },
    { key: 'customer', label: 'Customer', render: (r) => r.customers?.name ?? 'Walk-in' },
    {
      key: 'payment_method', label: 'Payment',
      render: (r) => <span className="capitalize text-muted-foreground">{PAYMENT_METHOD_LABELS[r.payment_method] ?? r.payment_method ?? '—'}</span>,
    },
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
        <DataTable
          columns={columns}
          data={sales}
          searchPlaceholder="Search by sale or invoice number…"
          searchKeys={['sale_number', 'invoice_number']}
          emptyMessage="No sales yet."
        />
      )}

      <NewSaleModal
        open={newModal}
        onOpenChange={setNewModal}
        customers={customers}
        inventory={inventory}
        items={pendingItems}
        setItems={setPendingItems}
        onProceed={handleProceed}
      />

      <PaymentModal
        open={paymentModal}
        onOpenChange={setPaymentModal}
        pendingSale={pendingSale}
        onConfirm={handleConfirmPayment}
        saving={createMutation.isPending}
        onBack={handleBack}
      />

      <ReceiptModal
        open={receiptModal}
        onOpenChange={(v) => { if (!v) setCompletedSaleId(null); setReceiptModal(v) }}
        saleId={completedSaleId}
      />

      <SaleDetailModal
        sale={detailSale}
        open={!!detailSale}
        onOpenChange={(v) => !v && setDetailSale(null)}
      />
    </div>
  )
}
