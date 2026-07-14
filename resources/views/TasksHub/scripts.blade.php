<script>
window.TaskAPI = (function($) {
    const defaultHeaders = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    // Inject Sanctum / CSRF token if available globally
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        defaultHeaders['X-CSRF-TOKEN'] = csrfToken;
    }

    function handleError(xhr) {
        console.error('[TaskAPI Error]', xhr);
        let msg = 'An unexpected error occurred.';
        if (xhr.responseJSON) {
            msg = xhr.responseJSON.message || xhr.responseJSON.error || msg;
        }
        if (window.Nexus && window.Nexus.notify) {
            Nexus.notify(msg, 'error');
        } else {
            alert('Error: ' + msg);
        }
    }

    return {
        get: function(endpoint, params = {}) {
            return $.ajax({
                url: '/api/v1' + endpoint,
                method: 'GET',
                data: params,
                headers: defaultHeaders
            }).fail(handleError);
        },
        
        post: function(endpoint, data = {}) {
            return $.ajax({
                url: '/api/v1' + endpoint,
                method: 'POST',
                data: data,
                headers: defaultHeaders
            }).fail(handleError);
        },
        
        patch: function(endpoint, data = {}) {
            return $.ajax({
                url: '/api/v1' + endpoint,
                method: 'PATCH',
                data: data,
                headers: defaultHeaders
            }).fail(handleError);
        },
        
        delete: function(endpoint, data = {}) {
            return $.ajax({
                url: '/api/v1' + endpoint,
                method: 'DELETE',
                data: data,
                headers: defaultHeaders
            }).fail(handleError);
        }
    };
})(jQuery);
</script>
