<script>
/**
 * WorkflowAPI - Centralized AJAX Helper for WorkflowsHub
 * Handles CSRF tokens, global loading states, and error notifications.
 */
window.WorkflowAPI = {
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    
    /**
     * Core AJAX Request Wrapper
     */
    request: function(endpoint, method = 'GET', data = null, options = {}) {
        return new Promise((resolve, reject) => {
            const ajaxOptions = {
                url: endpoint,
                type: method,
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    let errorMsg = 'An unexpected error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    if (options.showError !== false && window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify(errorMsg, 'error');
                    } else if (options.showError !== false) {
                        alert('Error: ' + errorMsg);
                    }
                    
                    reject({xhr, status, error, message: errorMsg});
                }
            };

            if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
                // If it's FormData (file uploads etc)
                if (data instanceof FormData) {
                    ajaxOptions.data = data;
                    ajaxOptions.processData = false;
                    ajaxOptions.contentType = false;
                } else {
                    ajaxOptions.data = JSON.stringify(data);
                    ajaxOptions.contentType = 'application/json';
                }
            }

            $.ajax(ajaxOptions);
        });
    },

    /**
     * Initialization & UI Bindings
     */
    init: function() {
        console.log('WorkflowAPI initialized.');
        this.bindTabNavigation();
    },

    bindTabNavigation: function() {
        $('.wf-nav-btn').on('click', function(e) {
            e.preventDefault();
            const target = $(this).data('target');
            
            // Update Active Tab Button
            $('.wf-nav-btn').removeClass('active');
            $(this).addClass('active');
            
            // Switch Tab Panes
            $('.wf-tab-pane').removeClass('show active');
            $(target).addClass('show active');
        });
    }
};

// Initialize when DOM is ready
$(document).ready(function() {
    window.WorkflowAPI.init();
});
</script>
