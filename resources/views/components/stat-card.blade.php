@props(['color' => 'gray', 'label' => '', 'value' => 0, 'icon' => '📊'])

<div class="p-4 bg-{{ $color }}-100 border-l-4 border-{{ $color }}-600 rounded-lg shadow-sm hover:shadow-md transition">
    <div class="flex items-center justify-between">
        <h4 class="text-sm font-semibold text-gray-700">{{ $label }}</h4>
        <span class="text-2xl">{{ $icon }}</span>
    </div>
    <p class="mt-2 text-3xl font-bold text-{{ $color }}-700">{{ $value }}</p>
</div>
