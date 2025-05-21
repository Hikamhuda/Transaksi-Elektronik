<x-filament::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @livewire(\App\Filament\Widgets\SalesChart::class)
        @livewire(\App\Filament\Widgets\TransactionsCountChart::class)
        @livewire(\App\Filament\Widgets\TopProductsChart::class)
    </div>
</x-filament::page>
