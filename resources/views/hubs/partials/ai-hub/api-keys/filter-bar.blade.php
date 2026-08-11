<!-- Quick Filter Tabs -->
<ul class="nav nav-pills nav-pills-custom mb-3 border-bottom border-secondary pb-3 gap-2" id="key-filter-pills">
    <li class="nav-item"><button class="nav-link active" data-filter="all" type="button">All</button></li>
    <li class="nav-item"><button class="nav-link text-success" data-filter="active" type="button">● Active</button></li>
    <li class="nav-item"><button class="nav-link text-warning" data-filter="cooldown" type="button">⚠ Cooldown</button></li>
    <li class="nav-item"><button class="nav-link text-danger" data-filter="errors" type="button">❌ Errors</button></li>
    <li class="nav-item"><button class="nav-link text-muted" data-filter="revoked" type="button">🔒 Revoked</button></li>
    <li class="nav-item"><button class="nav-link text-info" data-filter="primary" type="button">⭐ Primary Default</button></li>
</ul>

<!-- Advanced Filter Bar -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <div class="input-group input-group-sm" style="width: 260px;">
        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-search"></i></span>
        <input type="text" id="keys-search-input" class="form-control bg-dark border-secondary text-light" placeholder="Search by name, key, or prefix...">
    </div>
    <select id="keys-provider-filter" class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
        <option value="">All Providers</option>
        @foreach($providers as $p)
            <option value="{{ strtolower($p->name) }}">{{ $p->name }}</option>
        @endforeach
    </select>
    <select id="keys-sort-select" class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
        <option value="created_desc">Sort by: Newest Created</option>
        <option value="spend_desc">Sort by: Highest Spend</option>
        <option value="requests_desc">Sort by: Most Requests</option>
        <option value="priority_desc">Sort by: Highest Priority</option>
    </select>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let currentTabFilter = 'all';

        function applyApiKeyFilters() {
            const query = $('#keys-search-input').val().toLowerCase().trim();
            const providerFilter = $('#keys-provider-filter').val();

            $('.api-key-card-item').each(function() {
                const card = $(this);
                const isActive = card.data('active') === true || card.data('active') === 1;
                const isCooldown = card.data('cooldown') === true || card.data('cooldown') === 1;
                const isPrimary = card.data('default') === true || card.data('default') === 1;
                const errorCount = parseInt(card.data('errors') || 0);
                const providerName = (card.data('provider') || '').toString().toLowerCase();
                const cardText = card.text().toLowerCase();

                let tabMatch = true;
                if (currentTabFilter === 'active') tabMatch = isActive;
                else if (currentTabFilter === 'cooldown') tabMatch = isCooldown;
                else if (currentTabFilter === 'errors') tabMatch = errorCount > 0;
                else if (currentTabFilter === 'revoked') tabMatch = !isActive;
                else if (currentTabFilter === 'primary') tabMatch = isPrimary;

                let searchMatch = !query || cardText.indexOf(query) > -1;
                let providerMatch = !providerFilter || providerName.indexOf(providerFilter) > -1;

                card.toggle(tabMatch && searchMatch && providerMatch);
            });

            sortApiKeyCards();
        }

        function sortApiKeyCards() {
            const sortVal = $('#keys-sort-select').val();
            const container = $('#api-keys-container');
            const items = container.find('.api-key-card-item').get();

            items.sort(function(a, b) {
                const cardA = $(a);
                const cardB = $(b);
                if (sortVal === 'spend_desc') {
                    return parseFloat(cardB.data('cost') || 0) - parseFloat(cardA.data('cost') || 0);
                } else if (sortVal === 'requests_desc') {
                    return parseInt(cardB.data('reqs') || 0) - parseInt(cardA.data('reqs') || 0);
                } else if (sortVal === 'priority_desc') {
                    return parseInt(cardB.data('priority') || 0) - parseInt(cardA.data('priority') || 0);
                } else {
                    return (cardB.data('created') || 0) - (cardA.data('created') || 0);
                }
            });

            $.each(items, function(i, item) {
                container.append(item);
            });
        }

        $('#key-filter-pills button').on('click', function(e) {
            e.preventDefault();
            $('#key-filter-pills button').removeClass('active');
            $(this).addClass('active');
            currentTabFilter = $(this).data('filter');
            applyApiKeyFilters();
        });

        $('#keys-search-input').on('keyup input', applyApiKeyFilters);
        $('#keys-provider-filter').on('change', applyApiKeyFilters);
        $('#keys-sort-select').on('change', sortApiKeyCards);
    });
</script>
@endpush
