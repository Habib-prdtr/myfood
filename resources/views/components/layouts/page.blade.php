<x-layouts.app>
    <div class="relative">
        {{ $slot }}
        <livewire:components.main-menu />
    </div>
    <div id="toast-container" class="fixed bottom-5 right-5 space-y-3 z-50"></div>
</x-layouts.app>
