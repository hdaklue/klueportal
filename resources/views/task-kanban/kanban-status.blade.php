@props(['status'])
@use(App\Enums\FlowStatus)
@php
    $color = $status['color'];
@endphp

<div class="flex flex-col" x-data="{
    height: window.innerHeight,
    isMobile: window.innerWidth < 768,
    init() {
        this.updateHeight();
        window.addEventListener('resize', () => this.updateHeight());
    },
    updateHeight() {
        this.height = window.innerHeight - 500;
    }
}" wire:key='kanban-board-{{ $status['id'] }}'>
    @include(static::$headerView)
    <div class="md:scrollbar-hide mb-5 flex flex-shrink-0 flex-col overflow-y-auto overscroll-y-contain rounded-xl p-2 md:w-[18rem]"
        x-bind:style="isMobile ? '' : 'height:' + height + 'px'">
        @unless ($status['records'])
            <div class="flex items-center justify-center py-4 border border-gray-100 rounded-xl dark:border-gray-800"
                x-data="{ show: true }" x-show="show" @item-is-moving.window="show = false"
                @item-stopped-moving.window="show = true">
                <p class="w-2/3 text-center text-gray-400 text-2xs dark:text-gray-500">
                    @if ($this->canManageFlow)
                        No items assinged, you can drag and drop here!
                    @else
                        No items assinged!
                    @endif
                </p>
            </div>
        @endunless
        <div data-status-id="{{ $status['id'] }}" class="flex flex-1 flex-col gap-y-1.5 rounded-xl" wire:ignore
            x-bind:style="isMobile ? '' : 'height:' + height + 'px'">
            @if ($status['records'])
                @foreach ($status['records'] as $record)
                    <livewire:kanban.record :$record wire:key="record-{{ $record->getKey() }}" :status="$status['id']" />
                    {{-- @include(static::$recordView) --}}
                @endforeach
            @endif
        </div>

    </div>
</div>
