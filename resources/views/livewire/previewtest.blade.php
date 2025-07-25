<div x-data="designReviewApp()" x-init="init({})" @keydown.escape.window="!$wire.showCommentModal && handleEscape()"
    @keydown.arrow-up.window.prevent="handleArrowKeys($event)"
    @keydown.arrow-down.window.prevent="handleArrowKeys($event)"
    @keydown.arrow-left.window.prevent="handleArrowKeys($event)"
    @keydown.arrow-right.window.prevent="handleArrowKeys($event)"
    @open-comment-modal.window="$wire.openCommentModal($event.detail)" role="application"
    aria-label="Design Review Interface">
    <!-- Comment Detail Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-800/90 backdrop-blur-sm"
        x-show="showingComment" x-cloak role="dialog" aria-modal="true" aria-labelledby="comment-title">

        <!-- Comment container -->
        <div x-show="showingComment" x-transition:enter="transition ease-out duration-300"
            @click.outside="closeComment()" x-transition:enter-start="transform translate-y-full opacity-0"
            x-transition:enter-end="transform translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="transform translate-y-0 opacity-100"
            x-transition:leave-end="transform translate-y-full opacity-0"
            class="fixed bottom-0 left-1/2 z-50 h-4/5 w-full max-w-2xl -translate-x-1/2 transform rounded-t-xl bg-white p-6 shadow-2xl dark:bg-zinc-900 md:relative md:h-auto md:max-h-[80vh] md:rounded-xl"
            x-cloak>
            <div class="flex h-full flex-col text-zinc-600 dark:text-zinc-400">
                <!-- Header -->
                <div class="flex-shrink-0 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 id="comment-title" class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                            Comment Details
                        </h3>
                        <button @click="closeComment()"
                            class="rounded-lg p-2 text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                            aria-label="Close comment details">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-auto py-4">
                    <div class="space-y-4">
                        @if ($activeCommentId)
                            <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">Comment ID:</p>
                                <p class="font-mono text-sm text-zinc-900 dark:text-zinc-100">{{ $activeCommentId }}</p>
                            </div>
                            <!-- Add more comment content here -->
                        @else
                            <p class="text-center text-zinc-500 dark:text-zinc-400">No comment selected</p>
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex-shrink-0 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <button @click="closeComment()"
                        class="w-full rounded-lg bg-zinc-100 px-4 py-2 font-medium text-zinc-700 transition-colors hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Demo Container -->
    <div class="mx-auto max-w-4xl rounded-lg bg-white p-6 shadow-sm dark:bg-zinc-800">

        <h1 class="mb-2 text-2xl font-bold text-zinc-800 dark:text-white">Design Review Component Demo</h1>
        <p class="mb-4 text-zinc-600 dark:text-zinc-400">Click on any image to open the review modal. Click or drag on
            the image to add
            comments.</p>


        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($images as $image)
                <div class="flex cursor-pointer space-x-3 overflow-hidden rounded bg-zinc-100 p-2 transition-all hover:scale-105 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                    @click="openModal('{{ asset($image['url']) }}', @js($image['comments'] ?? []), '{{ $image['id'] }}')">
                    <div class="h-20 w-20 overflow-hidden">
                        <img src="{{ asset($image['url']) }}" alt="Design" class="h-auto w-full object-cover"
                            loading="lazy">
                    </div>
                    <div class="flex flex-col items-start justify-start space-y-1">
                        <h2 class="pb-1.5 text-xs font-semibold leading-5 text-zinc-900 dark:text-zinc-50">Snapchat
                            option 3 story size</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400"><span
                                class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Size:</span> 100 MB
                        </p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400"><span
                                class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Dimension:</span>
                            1200 x
                            1080</p>
                    </div>
                </div>
            @endforeach
            {{-- <div class="overflow-hidden transition-transform rounded cursor-pointer hover:scale-105"
                @click="openModal($wire.image, $wire.comments)">
                <img :src="$wire.image" alt="Design 3" class="object-cover w-full h-40">
            </div>
            <div class="overflow-hidden transition-transform rounded cursor-pointer hover:scale-105"
                @click="openModal($wire.image, $wire.comments)">
                <img :src="$wire.image" alt="Design 3" class="object-cover w-full h-40">
            </div> --}}
        </div>
    </div>

    <!-- Main Review Modal -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 flex items-center justify-center bg-black/90 p-4" @click="handleBackdropClick($event)"
        style="display: none;" role="dialog" aria-modal="true" aria-labelledby="modal-title">

        <div class="relative flex max-h-[95vh] max-w-[95vw] flex-col rounded-lg bg-white shadow-2xl transition-all duration-200 dark:bg-zinc-900"
            @click.stop :class="showingComment ? 'scale-95' : ''" role="document">

            <!-- Clean Modal Topbar -->
            <div
                class="flex items-center justify-between rounded-t-lg border-b border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Left: Icon + Title -->
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 dark:bg-sky-500/20">
                        <svg class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Design Review</h2>
                        <p class="hidden text-xs text-zinc-500 dark:text-zinc-400 sm:block">Click or drag to add
                            feedback</p>
                    </div>
                </div>

                <!-- Right: Close Button -->
                <button @click="handleClose()" @touchend.prevent="handleClose()"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    aria-label="Close image review modal">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Viewport - Available space for modal -->
            <div x-ref="scrollContainer" @wheel.prevent="handleWheel($event)" role="img"
                :aria-label="`Design image for review. Zoom level: ${Math.round(zoomLevel * 100)}%. ${zoomLevel > 1 ? 'Use arrow keys to navigate.' : 'Click or drag to add comments.'}`"
                :style="{
                    position: 'relative',
                    display: 'flex',
                    overflow: 'hidden',
                    background: 'rgba(0, 0, 0, 0.9)',
                    width: mainContainerWidth > 0 ? mainContainerWidth + 'px' : '95vw',
                    height: mainContainerHeight > 0 ? mainContainerHeight + 'px' : '90vh'
                }"
                <!-- Main Container - Fills viewport exactly -->
                <div x-ref="mainContainer"
                    style="position: relative; overflow: hidden; background: transparent; width: 100%; height: 100;">

                    <!-- Inner Wrapper - Absolute positioned, centered, scales with zoom -->
                    <div x-ref="innerWrapper"
                        style="position: absolute; left: 50%; top: 50%; background: transparent;"
                        :style="{
                            width: innerWrapperWidth > 0 ? innerWrapperWidth + 'px' : '400px',
                            height: innerWrapperHeight > 0 ? innerWrapperHeight + 'px' : '300px',
                            transform: `translate(calc(-50% + ${panX}px), calc(-50% + ${panY}px))`
                        }"
                        @mousedown.prevent="startSelection($event)" @touchstart.prevent="handleTouchStart($event)"
                        @mousemove="isDragging && throttledUpdateSelection($event)"
                        @touchmove="isDragging && handleTouchMove($event)" @mouseup="endSelection($event)"
                        @touchend="handleTouchEnd($event)" @mouseleave="isDragging && endSelection($event)"
                        @touchcancel="cancelSelection()" @contextmenu.prevent>

                        <!-- Image fills the inner wrapper completely -->
                        <img :src="currentImage" x-ref="image" @load="updateImageDimensions()"
                            style="
                                pointer-events: none;
                                display: block;
                                width: 100%;
                                height: 100%;
                                object-fit: fill;
                                transition: opacity 0.2s ease;
                                opacity: 1;
                                user-drag: none;
                                -webkit-user-drag: none;
                                -khtml-user-drag: none;
                                -moz-user-drag: none;
                                -o-user-drag: none;
                             "
                            alt="Design for review" draggable="false">

                        <!-- Comments Overlay (hidden when zoomed) -->
                        <template x-for="(comment, index) in visibleComments" :key="comment.id">
                            <div x-show="!isZoomed"
                                class="absolute cursor-pointer border-2 border-sky-500 bg-sky-500/20 transition-all duration-200 hover:z-10 hover:border-sky-600 hover:bg-sky-500/30 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                                :class="{ 'bg-sky-500/40 border-sky-700 z-20 shadow-lg': activeCommentId === comment.id }"
                                :style="`left: ${comment.x}%; top: ${comment.y}%; width: ${comment.width}%; height: ${comment.height}%; min-width: 20px; min-height: 20px;`"
                                @click="selectComment(comment)" @keydown.enter="selectComment(comment)"
                                @keydown.space.prevent="selectComment(comment)" tabindex="0" role="button"
                                :aria-label="`Comment ${index + 1}: ${comment.text?.slice(0, 50) || 'No text'}${comment.text?.length > 50 ? '...' : ''}`">
                                <span
                                    class="absolute -left-3 -top-3 flex h-6 w-6 items-center justify-center rounded-full bg-sky-500 text-xs font-bold text-white shadow-md"
                                    x-text="index + 1" aria-hidden="true"></span>
                            </div>
                        </template>

                        <!-- Selection Box -->
                        <template x-if="isSelecting">
                            <div class="pointer-events-none absolute animate-pulse border-2 border-dashed border-sky-500 bg-sky-500/10"
                                :style="`left: ${selectionBox.x}%; top: ${selectionBox.y}%; width: ${selectionBox.width}%; height: ${selectionBox.height}%;`"
                                role="presentation" aria-label="Selection area for comment">
                            </div>
                        </template>
                    </div>
                    <!-- End Inner Wrapper -->

                    <!-- Touch Navigation Arrows (Mobile Only, Zoomed Only) -->
                    <!-- Each button positioned individually to not block comment interactions -->

                    <!-- Up Arrow -->
                    <button x-show="isMobile && isZoomed" @click.stop="moveUp()" @touchend.stop.prevent="moveUp()"
                        style="
                                position: absolute;
                                top: 20px;
                                left: 50%;
                                transform: translateX(-50%);
                                width: 48px;
                                height: 48px;
                                background: rgba(9, 9, 11, 0.85);
                                border: 1px solid rgba(255, 255, 255, 0.2);
                                border-radius: 12px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                padding: 13px;
                                color: white;
                                backdrop-filter: blur(8px);
                                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                cursor: pointer;
                                z-index: 25;
                            "
                        aria-label="Move image up">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>

                    <!-- Down Arrow -->
                    <button x-show="isMobile && isZoomed" @click.stop="moveDown()"
                        @touchend.stop.prevent="moveDown()"
                        style="
                                position: absolute;
                                bottom: 20px;
                                left: 50%;
                                transform: translateX(-50%);
                                width: 48px;
                                height: 48px;
                                background: rgba(9, 9, 11, 0.85);
                                border: 1px solid rgba(255, 255, 255, 0.2);
                                border-radius: 12px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                padding: 13px;
                                color: white;
                                backdrop-filter: blur(8px);
                                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                cursor: pointer;
                                z-index: 25;
                            "
                        aria-label="Move image down">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Left Arrow -->
                    <button x-show="isMobile && isZoomed" @click.stop="moveLeft()"
                        @touchend.stop.prevent="moveLeft()"
                        style="
                                position: absolute;
                                left: 20px;
                                top: 50%;
                                transform: translateY(-50%);
                                width: 48px;
                                height: 48px;
                                background: rgba(9, 9, 11, 0.85);
                                border: 1px solid rgba(255, 255, 255, 0.2);
                                border-radius: 12px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                padding: 13px;
                                color: white;
                                backdrop-filter: blur(8px);
                                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                cursor: pointer;
                                z-index: 25;
                            "
                        aria-label="Move image left">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Right Arrow -->
                    <button x-show="isMobile && isZoomed" @click.stop="moveRight()"
                        @touchend.stop.prevent="moveRight()"
                        style="
                                position: absolute;
                                right: 20px;
                                top: 50%;
                                transform: translateY(-50%);
                                width: 48px;
                                height: 48px;
                                background: rgba(9, 9, 11, 0.85);
                                border: 1px solid rgba(255, 255, 255, 0.2);
                                border-radius: 12px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                padding: 13px;
                                color: white;
                                backdrop-filter: blur(8px);
                                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                cursor: pointer;
                                z-index: 25;
                            "
                        aria-label="Move image right">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                </div>
                <!-- End Main Container -->
            </div>
            <!-- End Viewport -->

            <!-- Modal Footer Toolbar -->
            <div
                class="flex items-center justify-center gap-3 rounded-b-lg border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Comment Filter -->
                <div class="relative" @click.outside="showCommentFilter = false">
                    <button @click="toggleCommentFilter" @touchend.prevent="toggleCommentFilter"
                        :class="showCommentFilter || hasActiveFilter ?
                            'bg-sky-500 hover:bg-sky-400 text-white' :
                            'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300'"
                        class="flex h-10 w-10 items-center justify-center rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-sky-500 sm:h-8 sm:w-8"
                        x-tooltip="'Filter comments'" aria-label="Toggle comment filter">
                        <svg class="h-5 w-5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Dropdown positioned above footer -->
                    <div x-show="showCommentFilter" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        x-trap="showCommentFilter"
                        class="absolute bottom-full left-1/2 z-[80] mb-2 hidden w-56 -translate-x-1/2 space-y-1 rounded-lg border border-zinc-200 bg-white p-3 shadow-lg dark:border-zinc-700 dark:bg-zinc-800 sm:block"
                        role="menu" aria-label="Comment filter options">
                        <div class="mb-2">
                            <h4 class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Filter Comments</h4>
                        </div>
                        <div class="max-h-48 space-y-2 overflow-y-auto">
                            <template x-for="(comment, index) in comments" :key="comment.id">
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded p-2 text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                                    role="menuitemcheckbox" :aria-checked="selectedCommentIds.includes(comment.id)">
                                    <input type="checkbox" :value="comment.id" x-model="selectedCommentIds"
                                        class="mt-0.5 rounded border-zinc-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-zinc-600">
                                    <span
                                        x-text="comment.text?.slice(0, 35) + (comment.text?.length > 35 ? '...' : '')"
                                        class="flex-1 text-zinc-700 dark:text-zinc-300"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Show/Hide Comments -->
                <button @click="toggleAllComments" @touchend.prevent="toggleAllComments"
                    :class="allCommentsHidden ?
                        'bg-sky-500 text-white hover:bg-sky-400' :
                        'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300'"
                    class="flex h-10 w-10 items-center justify-center rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-sky-500 sm:h-8 sm:w-8"
                    x-tooltip="allCommentsHidden ? 'Show all comments' : 'Hide all comments'"
                    :aria-label="allCommentsHidden ? 'Show all comments' : 'Hide all comments'">
                    <template x-if="allCommentsHidden">
                        <svg class="h-5 w-5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-5 0-9.27-3.11-10.5-7.5a10.05 10.05 0 013.03-4.57m3.39-2.05A9.953 9.953 0 0112 5c5 0 9.27 3.11 10.5 7.5a9.956 9.956 0 01-4.423 5.568M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <line x1="3" y1="3" x2="21" y2="21" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </template>
                    <template x-if="!allCommentsHidden">
                        <svg class="h-5 w-5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                        </svg>
                    </template>
                </button>

                <!-- Zoom Controls -->
                <div class="flex items-center gap-1">
                    <button @click="zoomOut()" @touchend.prevent="zoomOut()" :disabled="!canZoomOut"
                        :class="!canZoomOut ? 'opacity-50 cursor-not-allowed' : 'hover:bg-zinc-200 dark:hover:bg-zinc-700'"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:bg-zinc-800 dark:text-zinc-300 sm:h-8 sm:w-8"
                        x-tooltip="'Zoom out'" aria-label="Zoom out">
                        <svg class="h-5 w-5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path d="M8 11h6" />
                        </svg>
                    </button>

                    <button @click="resetZoom()" @touchend.prevent="resetZoom()"
                        :class="zoomLevel === 1 ? 'opacity-50' : 'hover:bg-zinc-200 dark:hover:bg-zinc-700'"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:bg-zinc-800 dark:text-zinc-300 sm:h-8 sm:w-8"
                        x-tooltip="zoomLevel > 1 ? 'Reset zoom (100%). Use arrow keys to navigate when zoomed.' : 'Reset zoom to 100%'"
                        aria-label="Reset zoom">
                        <span class="text-xs font-bold" x-text="`${Math.round(zoomLevel * 100)}%`"></span>
                    </button>

                    <button @click="zoomIn()" @touchend.prevent="zoomIn()" :disabled="!canZoomIn"
                        :class="!canZoomIn ? 'opacity-50 cursor-not-allowed' : 'hover:bg-zinc-200 dark:hover:bg-zinc-700'"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:bg-zinc-800 dark:text-zinc-300 sm:h-8 sm:w-8"
                        x-tooltip="'Zoom in'" aria-label="Zoom in">
                        <svg class="h-5 w-5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path d="M8 11h6" />
                            <path d="M11 8v6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Comment Filter Modal -->
    <div x-show="showCommentFilter && isMobile" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-[90] flex items-end bg-black/50 backdrop-blur-sm"
        @click="showCommentFilter = false" role="dialog" aria-modal="true" aria-labelledby="filter-modal-title">

        <div class="w-full rounded-t-3xl bg-white px-4 pb-8 pt-6 shadow-2xl dark:bg-zinc-900" @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="transform translate-y-full" x-transition:enter-end="transform translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="transform translate-y-0"
            x-transition:leave-end="transform translate-y-full" role="document">

            <!-- Handle -->
            <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-zinc-300 dark:bg-zinc-600" aria-hidden="true"></div>

            <!-- Header -->
            <div class="mb-4">
                <h3 id="filter-modal-title" class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Filter
                    Comments</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Select which comments to show</p>
            </div>

            <!-- Content -->
            <div class="max-h-[60vh] space-y-3 overflow-y-auto">
                <template x-for="(comment, index) in comments" :key="comment.id">
                    <label
                        class="flex cursor-pointer items-start gap-4 rounded-xl p-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        role="menuitemcheckbox" :aria-checked="selectedCommentIds.includes(comment.id)">
                        <input type="checkbox" :value="comment.id" x-model="selectedCommentIds"
                            class="mt-1 h-5 w-5 rounded border-zinc-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-zinc-600">
                        <div class="flex-1">
                            <span x-text="comment.text?.slice(0, 50) + (comment.text?.length > 50 ? '...' : '')"
                                class="block text-zinc-900 dark:text-zinc-100"></span>
                            <span x-text="`Comment ${index + 1}`"
                                class="text-xs text-zinc-500 dark:text-zinc-400"></span>
                        </div>
                    </label>
                </template>

                <template x-if="comments.length === 0">
                    <div class="py-8 text-center">
                        <p class="text-zinc-500 dark:text-zinc-400">No comments available</p>
                    </div>
                </template>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4">
                <button @click="showCommentFilter = false"
                    class="flex-1 rounded-xl bg-zinc-100 py-3 text-center font-medium text-zinc-700 transition-colors hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    type="button">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Comment Creation Modal -->
    @if ($showCommentModal)
        <div x-data="{
            show: @entangle('showCommentModal'),
            focusFirstInput() {
                this.$nextTick(() => {
                    const textarea = this.$el.querySelector('textarea');
                    if (textarea) {
                        textarea.focus();
                        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                    }
                });
            }
        }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" x-init="$watch('show', value => { if (value) focusFirstInput(); })"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-sm md:p-4"
            @keydown.escape.window="$wire.cancelComment()" role="dialog" aria-modal="true"
            aria-labelledby="comment-modal-title" <!-- Desktop Modal -->
            <div class="mx-4 hidden w-full max-w-md rounded-xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900 md:block"
                @click.stop role="document">
                <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                    <h3 id="comment-modal-title" class="text-lg font-semibold text-zinc-900 dark:text-white">Add
                        Comment</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $pendingComment['type'] === 'area' ? 'Add feedback for the selected area' : 'Add feedback for this point' }}
                    </p>
                </div>

                <div class="p-6">
                    <label for="comment-text-desktop" class="sr-only">Comment text</label>
                    <textarea id="comment-text-desktop" wire:model="commentText"
                        class="w-full resize-none rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm placeholder-zinc-400 transition-colors focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-sky-400"
                        rows="4" placeholder="Describe your feedback or suggestion..." autofocus aria-describedby="comment-help"></textarea>
                    <p id="comment-help" class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        Be specific and constructive in your feedback.
                    </p>
                </div>

                <div class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                    <button wire:click="cancelComment"
                        class="rounded-lg bg-zinc-100 px-4 py-2 text-sm font-medium text-zinc-700 transition-all duration-200 hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-zinc-500 active:scale-95 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        type="button">
                        Cancel
                    </button>
                    <button wire:click="saveNewComment" :disabled="!$wire.commentText.trim()"
                        class="rounded-lg bg-sky-500 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                        type="button">
                        Save Comment
                    </button>
                </div>
            </div>

            <!-- Mobile Modal (Bottom Sheet) -->
            <div class="fixed inset-0 z-[70] flex items-end md:hidden">
                <div class="w-full rounded-t-3xl bg-white px-4 pb-8 pt-6 shadow-2xl dark:bg-zinc-900" @click.stop
                    role="document">
                    <!-- Handle -->
                    <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-zinc-300 dark:bg-zinc-600" aria-hidden="true">
                    </div>

                    <!-- Header -->
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Add Feedback</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $pendingComment['type'] === 'area' ? 'Add feedback for the selected area' : 'Add feedback for this point' }}
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="space-y-4">
                        <label for="comment-text-mobile" class="sr-only">Comment text</label>
                        <textarea id="comment-text-mobile" wire:model="commentText"
                            class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-4 py-3 text-base placeholder-zinc-400 transition-colors focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 dark:focus:border-sky-400"
                            rows="4" placeholder="Describe your feedback or suggestion..." autofocus
                            aria-describedby="comment-help-mobile"></textarea>
                        <p id="comment-help-mobile" class="text-xs text-zinc-500 dark:text-zinc-400">
                            Be specific and constructive in your feedback.
                        </p>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-2">
                            <button wire:click="cancelComment"
                                class="flex-1 rounded-xl bg-zinc-100 py-3 text-center font-medium text-zinc-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-zinc-500 active:scale-95 dark:bg-zinc-800 dark:text-zinc-300"
                                type="button">
                                Cancel
                            </button>
                            <button wire:click="saveNewComment" :disabled="!$wire.commentText.trim()"
                                class="flex-1 rounded-xl bg-sky-500 py-3 text-center font-medium text-white shadow-lg shadow-sky-500/25 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-sky-500 active:scale-95 disabled:opacity-50"
                                type="button">
                                Save Comment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
