@extends('layout.admin')

@section('title', 'Failed emails | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <div>
        <h1>Failed emails</h1>
        <p class="dash-sub">Emails the background worker couldn't send after 3 tries.</p>
    </div>
    @if ($emails->isNotEmpty())
        <form method="post" action="{{ route('admin.emails.failed.retry-all') }}">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="fas fa-rotate-right" aria-hidden="true"></i> Retry all</button>
        </form>
    @endif
</div>

@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif

@if ($stuck)
    <div class="alert alert-warning" role="alert">
        <strong>{{ $stuck }} {{ \Illuminate\Support\Str::plural('email', $stuck) }} waiting for over {{ \App\Services\Emails\EmailQueue::STUCK_MINUTES }} minutes.</strong>
        Is the email worker running? Start it with <code>php artisan queue:work --queue=emails,default</code>.
    </div>
@elseif ($waiting)
    <p class="form-help fe-waiting">{{ $waiting }} {{ \Illuminate\Support\Str::plural('email', $waiting) }} waiting to send.</p>
@endif

@if ($emails->isEmpty())
    <div class="panel">
        <div class="dash-empty">
            <i class="fas fa-envelope-circle-check" aria-hidden="true"></i>
            <p>No failed emails. Everything was sent.</p>
        </div>
    </div>
@else
    <p class="form-help fe-help">Fix the cause first (for example the mail settings in <code>.env</code>), then use <strong>Retry all</strong>. Retried emails go back to the queue and are sent by the worker.</p>
    <div class="panel-table">
        <table class="table fe-table">
            <thead>
                <tr>
                    <th scope="col">Email</th>
                    <th scope="col">To</th>
                    <th scope="col">Why it failed</th>
                    <th scope="col">Failed</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($emails as $email)
                    <tr>
                        <td>
                            <strong>{{ $email->type }}</strong>
                            @if ($email->order)
                                <small class="fe-sub">
                                    @if (!$email->order->trashed() && auth('admin')->user()->hasPermission('orders'))
                                        <a href="{{ route('admin.orders.show', [$email->order->encryptedId()]) }}">Order {{ $email->order->order_number }}</a>
                                    @else
                                        Order {{ $email->order->order_number }}
                                    @endif
                                </small>
                            @endif
                        </td>
                        <td class="fe-to">{{ $email->to ? implode(', ', $email->to) : '—' }}</td>
                        <td>
                            {{ $email->reason }}
                            @if ($email->detail)
                                <details class="fe-detail"><summary>Technical detail</summary><code>{{ \Illuminate\Support\Str::limit($email->detail, 400) }}</code></details>
                            @endif
                        </td>
                        <td class="text-nowrap"><time datetime="{{ $email->failed_at->toIso8601String() }}" title="{{ $email->failed_at->format('j M Y, g:i a') }}">{{ $email->failed_at->diffForHumans() }}</time></td>
                        <td class="text-right fe-actions">
                            <form method="post" action="{{ route('admin.emails.failed.retry', $email->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-light">Retry</button>
                            </form>
                            <form method="post" action="{{ route('admin.emails.failed.destroy', $email->id) }}" onsubmit="return confirm('Delete this email? It won\'t be sent.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link-button fe-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
