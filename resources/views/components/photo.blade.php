@props(['path' => null, 'name' => '', 'avatar' => false])
@if($path)
    <img @class(['thumbnail', 'avatar' => $avatar]) src="{{ Storage::disk('public')->url($path) }}" alt="{{ $name }}" loading="lazy">
@else
    <span @class(['thumbnail', 'placeholder', 'avatar' => $avatar]) aria-label="No image">{{ mb_strtoupper(mb_substr($name, 0, 1)) ?: '—' }}</span>
@endif
