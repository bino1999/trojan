export default function ServiceJobs() {
  return (
    <div>
      <h1 className="text-2xl font-semibold mb-1">Service Jobs</h1>
      <p className="text-muted-foreground text-sm mb-6">Job cards for vehicle servicing</p>
      {/* TODO: job list with status filter (open / in-progress / completed / invoiced) */}
      {/* TODO: create job → select/create customer → select/create vehicle */}
      {/* TODO: job detail — add items from inventory (auto-decrements stock), add labor */}
      {/* TODO: mark completed → generate PDF invoice */}
    </div>
  )
}
