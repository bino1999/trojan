export default function Adjustments() {
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-1">Stock Adjustments</h1>
      <p className="text-muted-foreground text-sm mb-6">Write off damaged, expired, or lost stock</p>
      {/* TODO: adjustments list */}
      {/* TODO: new adjustment form — select inventory item, qty change (negative = write-off) */}
      {/* TODO: reason code (damaged / expired / lost / correction / other) */}
      {/* TODO: notes required when reason = other */}
    </div>
  )
}
