@include('hubs.partials.ai-hub.providers.filter-bar')
@include('hubs.partials.ai-hub.providers.cards-grid')
@include('hubs.partials.ai-hub.providers.drawers.add-edit')

@push('scripts')
<script>
    function pingProvider(id) {
        window.Nexus.showTaskLoader();
        // Simulate API ping
        setTimeout(() => {
            window.Nexus.hideTaskLoader();
            window.Nexus.notify('Ping successful: 124ms', 'success');
        }, 1000);
    }
</script>
@endpush
