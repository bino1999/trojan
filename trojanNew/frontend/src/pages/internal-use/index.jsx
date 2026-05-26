import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { Plus, AlertTriangle } from 'lucide-react'
import api from '@/lib/api'
import { formatDate } from '@/lib/utils'
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

function NewRecordModal({ open, onOpenChange, products, inventory, onSave, saving }) {
  const [productId, setProductId] = useState('')
  const [qty, setQty] = useState(1)
  const [purpose, setPurpose] = useState('')
  const [notes, setNotes] = useState('')

  function handleOpen(isOpen) {
    if (isOpen) { setProductId(''); setQty(1); setPurpose(''); setNotes('') }
    onOpenChange(isOpen)
  }

  const stockItem = inventory.find((i) => i.product_id === productId)
  const currentStock = stockItem?.qty_in_stock ?? null
  const overStock = currentStock !== null && Number(qty) > currentStock

  function handleSubmit(e) {
    e.preventDefault()
    onSave({ product_id: productId, qty_used: Number(qty), purpose, notes })
  }

  return (
    <Dialog open={open} onOpenChange={handleOpen}>
      <DialogContent className="max-w-md">
        <DialogHeader><DialogTitle>Log Internal Use</DialogTitle></DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-3">
          <div>
            <Label>Product *</Label>
            <Select value={productId} onValueChange={setProductId} required>
              <SelectTrigger className="mt-1"><SelectValue placeholder="Select product…" /></SelectTrigger>
              <SelectContent>
                {products.filter((p) => p.is_active !== false).map((p) => (
                  <SelectItem key={p.id} value={p.id}>{p.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>
            {currentStock !== null && (
              <p className="text-xs text-muted-foreground mt-1">Current stock: <strong>{currentStock}</strong></p>
            )}
          </div>
          <div>
            <Label>Quantity Used *</Label>
            <Input type="number" min="1" value={qty} onChange={(e) => setQty(e.target.value)} required className="mt-1" />
            {overStock && (
              <p className="text-xs text-yellow-700 flex items-center gap-1 mt-1">
                <AlertTriangle className="h-3 w-3" /> Qty exceeds current stock ({currentStock})
              </p>
            )}
          </div>
          <div>
            <Label>Purpose *</Label>
            <Input value={purpose} onChange={(e) => setPurpose(e.target.value)} required className="mt-1" placeholder="e.g. Workshop maintenance" />
          </div>
          <div>
            <Label>Notes</Label>
            <Textarea value={notes} onChange={(e) => setNotes(e.target.value)} rows={2} className="mt-1" />
          </div>
          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
            <Button type="submit" disabled={saving || !productId || !purpose}>{saving ? 'Saving…' : 'Log Use'}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}

export default function InternalUse() {
  const { role } = useAuthStore()
  const qc = useQueryClient()
  const [modal, setModal] = useState(false)

  const { data: records = [], isLoading } = useQuery({
    queryKey: ['internal-use'],
    queryFn: () => api.get('/internal-use'),
  })
  const { data: products = [] } = useQuery({
    queryKey: ['products'],
    queryFn: () => api.get('/products'),
  })
  const { data: inventory = [] } = useQuery({
    queryKey: ['inventory'],
    queryFn: () => api.get('/inventory'),
  })

  const createMutation = useMutation({
    mutationFn: (data) => api.post('/internal-use', data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['internal-use'] })
      qc.invalidateQueries({ queryKey: ['inventory'] })
      setModal(false)
      toast({ title: 'Internal use logged', variant: 'success' })
    },
    onError: (err) => toast({ title: 'Error', description: err.message, variant: 'destructive' }),
  })

  const canCreate = ['admin', 'manager', 'warehouse', 'technician'].includes(role)

  const columns = [
    { key: 'product', label: 'Product', render: (r) => <span className="font-medium">{r.products?.name ?? r.product_id}</span> },
    { key: 'qty_used', label: 'Qty Used' },
    { key: 'purpose', label: 'Purpose', render: (r) => r.purpose || '—' },
    { key: 'user', label: 'Used By', render: (r) => r.user_profiles?.email ?? r.used_by ?? '—' },
    { key: 'created_at', label: 'Date', render: (r) => formatDate(r.created_at) },
    { key: 'notes', label: 'Notes', render: (r) => r.notes || '—' },
  ]

  return (
    <div>
      <PageHeader
        title="Internal Use"
        description="Log items consumed within the workshop (non-billable)"
        action={canCreate && (
          <Button onClick={() => setModal(true)}>
            <Plus className="h-4 w-4 mr-2" /> Log Use
          </Button>
        )}
      />

      {isLoading ? (
        <p className="text-muted-foreground text-sm">Loading…</p>
      ) : (
        <DataTable
          columns={columns}
          data={records}
          searchPlaceholder="Search by product or purpose…"
          searchKeys={['purpose']}
          emptyMessage="No internal use records yet."
        />
      )}

      <NewRecordModal
        open={modal}
        onOpenChange={setModal}
        products={products}
        inventory={inventory}
        onSave={(data) => createMutation.mutate(data)}
        saving={createMutation.isPending}
      />
    </div>
  )
}
