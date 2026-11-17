@extends('layout.admin')

@section('title', 'Activity log | Coda Admin')

@section('content')
<div class="page-title">
    <h1>Activity log</h1>
</div>

<div class="panel-table">
    <div class="toolbar default-toolbar space-between">
        <ul class="order-navigation">
            <li class="{{ $group ? '' : 'selected' }}"><a href="{{ route('admin.activity') }}">All</a></li>
            @foreach (\App\Models\ActivityLog::GROUPS as $key => [$label])
                <li class="{{ $group === $key ? 'selected' : '' }}"><a href="{{ route('admin.activity', ['type' => $key]) }}">{{ $label }}</a></li>
            @endforeach
        </ul>
    </div>

    @if ($entries->isEmpty())
        <div class="dash-empty">
            <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
            <p>No activity yet. Changes to orders, prices, stock and staff access will show up here.</p>
        </div>
    @else
        <table class="table activity-table">
            <thead>
                <tr>
                    <th scope="col">When</th>
                    <th scope="col">Who</th>
                    <th scope="col">What changed</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                    <tr>
                        <td class="activity-when"><time datetime="{{ $entry->created_at->toIso8601String() }}" title="{{ $entry->created_at->format('M j, Y g:i a') }}">{{ $entry->created_at->format('M j, g:i a') }}</time></td>
                        <td>{{ $entry->actorName() }}</td>
                        <td>
                            @if ($entry->subject_type === \App\Models\Order::class && $entry->subject_id)
                                <a class="link-primary" href="{{ route('admin.orders.show', [encrypt($entry->subject_id)]) }}">{{ $entry->description }}</a>
                            @else
                                {{ $entry->description }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="m-t-2">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
