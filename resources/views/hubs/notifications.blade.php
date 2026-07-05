@extends('layouts.app')
@section('page_title', 'Notifications')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="text-light mb-1">Notifications</h2>
            <p class="text-muted mb-0">Recent alerts and activity from your account.</p>
        </div>
        <a href="{{ route('hub.dashboard') }}" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>

    <div class="card bg-glass border-secondary border-1">
        <div class="card-body p-4">
            @if($notifications->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-regular fa-bell-slash fa-2x text-muted mb-3"></i>
                    <div class="text-muted">No notifications found.</div>
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action d-flex flex-column gap-2 {{ $notification->is_read ? '' : 'bg-secondary bg-opacity-10' }}" style="border-radius: 12px; margin-bottom: 10px;">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold text-light">{{ $notification->title }}</div>
                                    <div class="text-muted small">{{ $notification->body }}</div>
                                </div>
                                <span class="badge rounded-pill {{ $notification->is_read ? 'bg-secondary' : 'bg-primary' }}" style="font-size: 0.75rem;">{{ $notification->is_read ? 'Read' : 'Unread' }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-muted small">
                                <span>{{ $notification->created_at?->diffForHumans() ?? '' }}</span>
                                @if($notification->action_buttons && is_array($notification->action_buttons) && count($notification->action_buttons))
                                    <div class="d-flex gap-2">
                                        @foreach($notification->action_buttons as $button)
                                            <a href="{{ $button['url'] ?? '#' }}" class="btn btn-sm btn-outline-primary">{{ $button['label'] ?? 'Open' }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
