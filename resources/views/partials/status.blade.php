@php($status = $status ?? 'active')
<span class="badge" data-status="{{ $status }}">{{ status_label($group ?? 'listing_statuses', $status) }}</span>
