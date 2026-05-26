export default function Inventory() {
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-1">Inventory</h1>
      <p className="text-muted-foreground text-sm mb-6">Live stock ledger — quantities and pricing per batch</p>
      {/* TODO: inventory table (product, supplier, qty_in_stock, company_price, selling_price, reorder_level) */}
      {/* TODO: low stock highlight (qty <= reorder_level) */}
      {/* TODO: filter by category / supplier */}
    </div>
  )
}
