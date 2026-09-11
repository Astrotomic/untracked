@if ($url !== null)
    <img
        src="{{ $url }}"
        alt=""
        loading="lazy"
        referrerpolicy="no-referrer"
        {{ $attributes->class(['size-4 shrink-0 rounded-sm']) }}
    >
@endif
