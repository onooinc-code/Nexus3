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

// TasksHub Global Tab Persistence & Fullscreen Control (Sidebar Collapse & State Memory)
(function($) {
    $(function() {
        const STORAGE_KEY_TAB = 'taskshub_active_tab';
        const STORAGE_KEY_FULLSCREEN = 'taskshub_fullscreen_active';

        // 1. Restore Saved Active Tab On Load
        let savedTab = localStorage.getItem(STORAGE_KEY_TAB);
        if (savedTab) {
            let $btn = $('#taskhub-nav button[data-bs-target="' + savedTab + '"]');
            if (!$btn.length) {
                $btn = $('#' + savedTab);
            }
            if ($btn.length) {
                $('#taskhub-nav .nav-link').removeClass('active');
                $('.tab-content .tab-pane').removeClass('show active');

                $btn.addClass('active');
                let targetPane = $btn.attr('data-bs-target');
                if (targetPane && $(targetPane).length) {
                    $(targetPane).addClass('show active');
                }
            }
        }

        // Save Tab on Switch
        $(document).on('shown.bs.tab', '#taskhub-nav button[data-bs-toggle="tab"]', function(e) {
            let targetPane = $(e.target).attr('data-bs-target') || $(e.target).attr('id');
            if (targetPane) {
                localStorage.setItem(STORAGE_KEY_TAB, targetPane);
            }
        });

        // 2. Fullscreen Toggle & Sidebar Hiding
        const btnFullscreen = document.getElementById('btn-toggle-fullscreen');
        const iconFullscreen = document.getElementById('fullscreen-icon');

        function updateFullscreenState(isFS) {
            if (isFS) {
                $('body').addClass('taskshub-fullscreen-active');
                if (iconFullscreen) iconFullscreen.className = 'fa-solid fa-compress text-info';
                if (btnFullscreen) btnFullscreen.style.borderColor = 'rgba(56, 189, 248, 0.4)';
            } else {
                $('body').removeClass('taskshub-fullscreen-active');
                if (iconFullscreen) iconFullscreen.className = 'fa-solid fa-expand';
                if (btnFullscreen) btnFullscreen.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            }
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
                const elem = document.documentElement;
                if (elem.requestFullscreen) elem.requestFullscreen();
                else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
                else if (elem.msRequestFullscreen) elem.msRequestFullscreen();
                localStorage.setItem(STORAGE_KEY_FULLSCREEN, 'true');
                updateFullscreenState(true);
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                else if (document.msExitFullscreen) document.msExitFullscreen();
                localStorage.setItem(STORAGE_KEY_FULLSCREEN, 'false');
                updateFullscreenState(false);
            }
        }

        if (btnFullscreen) {
            btnFullscreen.addEventListener('click', toggleFullscreen);
        }

        $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange', function() {
            const isFS = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement);
            updateFullscreenState(isFS);
            localStorage.setItem(STORAGE_KEY_FULLSCREEN, isFS ? 'true' : 'false');
        });

        // Restore Fullscreen State if active
        if (localStorage.getItem(STORAGE_KEY_FULLSCREEN) === 'true') {
            updateFullscreenState(true);
            const autoFSHandler = function() {
                if (localStorage.getItem(STORAGE_KEY_FULLSCREEN) === 'true' && !document.fullscreenElement) {
                    toggleFullscreen();
                }
                $(document).off('click', autoFSHandler);
            };
            $(document).on('click', autoFSHandler);
        }
    });
})(jQuery);
</script>
