<div class="btn-group">
    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Export
    </button>
    <ul class="dropdown-menu">
        <li>
            <a href="{{ route('user.export.pdf', $entry->id) }}" class="btn btn-sm btn-danger">
                <i class="la la-file-pdf-o"></i> PDF
            </a>
        </li>
        <li>
            <a href="{{ route('user.export.csv', $entry->id) }}" class="btn btn-sm btn-info">
                <i class="la la-file-text-o"></i> CSV
            </a>
        </li>
        <li>
            <a href="{{ route('user.export.excel', $entry->id) }}" class="btn btn-sm btn-success">
                <i class="la la-file-excel-o"></i> Excel
            </a>
        </li>
    </ul>
</div>
