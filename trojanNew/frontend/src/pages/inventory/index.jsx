import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { AlertTriangle, Search } from 'lucide-react'
import api from '@/lib/api'
import { formatCurrency, formatDate, cn } from '@/lib/utils'
import PageHeader from '@/components/shared/PageHeader'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

export default function Inventory() {
  const [query, setQuery] = useState('')
  const [lowStockOnly, setLowStockOnly] = useState(false)

  const { data: inventory = [], isLoading } = useQuery({
    queryKey: ['inventory'],
    queryFn: () => api.get('/inventory'),
  })

  const filtered = inventory.filter((item) => {
    const name = item.products?.name ?? ''
    const sku = item.products?.sku ?? ''
    const matchesQuery = query === '' || name.toLowerCase().includes(query.toLowerCase()) || sku.toLowerCase().includes(query.toLowerCase())
    const matchesLowStock = !lowStockOnly || item.qty_in_stock <= item.reorder_level
    return matchesQuery && matchesLowStock
  })

  const lowCount = inventory.filter((i) => i.qty_in_stock <= i.reorder_level).length

  return (
    <div>
      <PageHeader
        title="Inventory"
        description="Live stock ledger — current quantities and pricing"
      />

      {lowCount > 0 && (
        <div className="mb-4 flex items-center gap-2 rounded-md border border-yellow-200 bg-yellow-50 px-4 py-2 text-sm text-yellow-800">
          <AlertTriangle className="h-4 w-4 shrink-0" />
          <span><strong>{lowCount}</strong> product{lowCount !== 1 ? 's are' : ' is'} at or below reorder level.</span>
          <button className="ml-auto underline text-xs" onClick={() => setLowStockOnly(true)}>Show only</button>
        </div>
      )}

      <div className="flex gap-2 mb-3">
        <div className="relative flex-1 max-w-xs">
          <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Search by name or SKU…"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            className="pl-8"
          />
        </div>
        <Button
          variant={lowStockOnly ? 'default' : 'outline'}
          size="sm"
          onClick={() => setLowStockOnly((v) => !v)}
          className="gap-1.5"
        >
          <AlertTriangle className="h-3.5 w-3.5" />
          Low Stock
        </Button>
      </div>

      {isLoading ? (
        <p className="text-muted-foreground text-sm">Loading…</p>
      ) : (
        <div className="rounded-md border overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-muted/50">
              <tr>
                <th className="px-4 py-3 text-left font-medium text-muted-foreground">Product</th>
                <th className="px-4 py-3 text-left font-medium text-muted-foreground">SKU</th>
                <th className="px-4 py-3 text-left font-medium text-muted-foreground">Category</th>
                <th className="px-4 py-3 text-right font-medium text-muted-foreground">Bought</th>
                <th className="px-4 py-3 text-right font-medium text-muted-foreground">In Stock</th>
                <th className="px-4 py-3 text-right font-medium text-muted-foreground">Sold</th>
                <th className="px-4 py-3 text-right font-medium text-muted-foreground">Reorder</th>
                <th className="px-4 py-3 text-right font-medium text-muted-foreground">Selling Price</th>
                <th className="px-4 py-3 text-left font-medium text-muted-foreground">Last Updated</th>
                <th className="px-4 py-3 text-left font-medium text-muted-foreground">Status</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr>
                  <td colSpan={10} className="px-4 py-8 text-center text-muted-foreground">No inventory records found.</td>
                </tr>
              ) : (
                filtered.map((item) => {
                  const isLow = item.qty_in_stock <= item.reorder_level
                  return (
                    <tr key={item.id} className={cn('border-t transition-colors', isLow ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-muted/30')}>
                      <td className="px-4 py-3 font-medium">{item.products?.name ?? '—'}</td>
                      <td className="px-4 py-3 text-muted-foreground">{item.products?.sku ?? '—'}</td>
                      <td className="px-4 py-3">{item.products?.category ?? '—'}</td>
                      <td className="px-4 py-3 text-right text-muted-foreground">{item.qty_bought ?? 0}</td>
                      <td className={cn('px-4 py-3 text-right font-semibold', isLow && 'text-yellow-700')}>
                        {item.qty_in_stock}
                        {isLow && <AlertTriangle className="inline h-3.5 w-3.5 ml-1 text-yellow-600" />}
                      </td>
                      <td className="px-4 py-3 text-right text-muted-foreground">{item.qty_sold ?? 0}</td>
                      <td className="px-4 py-3 text-right text-muted-foreground">{item.reorder_level ?? '—'}</td>
                      <td className="px-4 py-3 text-right">{formatCurrency(item.selling_price)}</td>
                      <td className="px-4 py-3 text-muted-foreground">{formatDate(item.updated_at)}</td>
                      <td className="px-4 py-3">
                        {isLow
                          ? <Badge variant="warning">Low Stock</Badge>
                          : <Badge variant="success">OK</Badge>}
                      </td>
                    </tr>
                  )
                })
              )}
            </tbody>
          </table>
        </div>
      )}

      {filtered.length > 0 && (
        <p className="text-xs text-muted-foreground mt-2">
          {filtered.length} of {inventory.length} record{inventory.length !== 1 ? 's' : ''}
        </p>
      )}
    </div>
  )
}
