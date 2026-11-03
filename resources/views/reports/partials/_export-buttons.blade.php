<div class="rpt-actions" role="group" aria-label="Export report">
    <a href="{{ route($report['url'].'-print', $query) }}" target="_blank" class="rpt-btn rpt-btn-print btn-print">
        <i class="ri-printer-line"></i> Print
    </a>
    <a href="{{ route($report['url'].'-pdf', $query) }}" class="rpt-btn rpt-btn-pdf btn-pdf">
        <i class="ri-file-pdf-2-line"></i> PDF
    </a>
    <a href="{{ route($report['url'].'-excel', $query) }}" class="rpt-btn rpt-btn-excel btn-excel">
        <i class="ri-file-excel-2-line"></i> Excel
    </a>
</div>
