<div id="{{ $record->getKey() }}"
    class="record dark:bg-{{ $color }}-900/5 bg-{{ $color }}-50 @can('manageFlows', filament()->getTenant())
     cursor-move
     @endcan relative overflow-hidden rounded-lg px-2 py-4 text-base font-medium text-gray-800 dark:text-gray-200"
    @if ($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}, true) < 3) x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-gray-100', 'dark:bg-gray-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-gray-100', 'dark:bg-gray-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        " @endif>


    <div class="flex flex-row justify-between gap-2">
        <div class="">
            <a
                class="text-{{ $color }}-800 flex font-semibold leading-snug hover:cursor-pointer lg:text-3xl dark:text-gray-300">{{ $record->{static::$recordTitleAttribute} }}</a>
        </div>
        <div class="flex flex-row -space-x-2">
            @foreach ($record->participants as $participant)
                <div class="h-6 w-6 rounded-full" x-tooltip="name" x-data="{
                    name: '{{ $participant->name }}'
                }">
                    <img class="w-full rounded-full border border-gray-700"
                        src="{{ filament()->getUserAvatarUrl($participant) }}">
                </div>
            @endforeach
        </div>
    </div>
    <div class="mt-1 flex flex-row gap-x-1">
        <div class="flex gap-x-1 text-xs font-medium">
            <span class="font-medium">Due:</span>
            <span>{{ $record->due_date->toDateString() }}</span>
        </div>
    </div>
    {{-- <div class="py-2 text-sm font-light prose ps-2 dark:text-gray-400">
        Lorem ipsum, dolor sit amet consectetur adipisicing elit.
    </div> --}}

    @can('manageMembers', $record)
        <div x-data class="b flex justify-end px-2 pt-2 text-sm">
            @include('role.manage-action')
        </div>
    @endcan

    {{-- <div class="absolute bottom-0 w-full h-1 start-0">
        <div class="relative h-0.5">
            <div class="dark:bg-{{ $color }}-800 bg-{{ $color }}-400 absolute start-0 top-0 h-full"
                style="width: {{ $this->getProgressPercentage($record) }}%">
            </div>
        </div>
    </div> --}}
</div>
