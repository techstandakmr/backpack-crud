{{-- Custom Table Widget for Show Page --}}
<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ $widget['label'] ?? 'Related Data' }}</h5>
    </div>
    <div class="card-body">
        @if(isset($widget['data']) && count($widget['data']) > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            @foreach($widget['columns'] as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($widget['data'] as $row)
                            <tr>
                                @foreach($widget['columns'] as $column)
                                    <td>
                                        @if(isset($column['type']) && $column['type'] === 'link')
                                            <a href="{{ $row[$column['link_key']] ?? '#' }}" target="_blank">
                                                {{ $row[$column['name']] ?? '-' }}
                                            </a>
                                        @elseif(isset($column['type']) && $column['type'] === 'badge')
                                            <span class="badge badge-{{ $row[$column['badge_class_key']] ?? 'secondary' }}">
                                                {{ $row[$column['name']] ?? '-' }}
                                            </span>
                                        @else
                                            {{ $row[$column['name']] ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No data available</p>
        @endif
    </div>
</div>