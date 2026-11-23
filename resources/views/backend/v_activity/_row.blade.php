@php
    $icons = [
        'login' => ['mdi-login', '#2e9bbd'], 'logout' => ['mdi-logout', '#888'],
        'create' => ['mdi-plus-circle', '#27ae60'], 'update' => ['mdi-pencil', '#c9821f'],
        'delete' => ['mdi-delete', '#c0392b'], 'view' => ['mdi-eye', '#666'], 'export' => ['mdi-download', '#555'],
    ];
    $ic = $icons[$log->action_type] ?? ['mdi-information', '#666'];
@endphp
<tr data-id="{{ $log->id }}">
    <td class="text-center"><i class="mdi {{ $ic[0] }}" style="font-size:22px;color:{{ $ic[1] }}"></i></td>
    <td>{{ $log->description }}</td>
    <td><span class="badge badge-light text-uppercase">{{ $log->module }}</span></td>
    <td>
        @if ($log->user)
            {{ $log->user->name }}
        @elseif (!empty($log->properties['customer_nama']))
            {{ $log->properties['customer_nama'] }} <span class="badge badge-light">Customer</span>
        @elseif (!empty($log->properties['customer_id']))
            <span class="text-muted">Customer #{{ $log->properties['customer_id'] }}</span>
        @elseif (!empty($log->properties['customer_email']))
            <span class="text-muted">{{ $log->properties['customer_email'] }}</span>
        @else
            —
        @endif
    </td>
    <td><small class="text-muted">{{ $log->ip_address }}</small></td>
    <td><small class="js-ago" data-ts="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->diffForHumans() }}</small><br><small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small></td>
</tr>
