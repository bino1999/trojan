export default function Purchases() {
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-1">Purchase Orders</h1>
      <p className="text-muted-foreground text-sm mb-6">Receive stock from suppliers</p>
      {/* TODO: PO list with status filter (pending / received / cancelled) */}
      {/* TODO: create PO → select supplier → add line items from product master → set qty, company_price, selling_price */}
      {/* TODO: mark as received → auto-creates inventory records */}
      {/* TODO: PO detail view */}
    </div>
  )
}
