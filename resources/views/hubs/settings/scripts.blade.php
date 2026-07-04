@push('scripts')
<script>
$(document).ready(function() {

    function showToast(type, message) {
        const id = 'toast-' + Date.now();
        const colorMap = { success: '#22c55e', danger: '#ef4444', warning: '#fb923c', info: '#3b82f6' };
        const color = colorMap[type] || colorMap.info;
        const html = `<div id="${id}" style="position:fixed;top:20px;right:20px;z-index:9999;background:rgba(22,27,34,0.97);border:1px solid ${color}40;border-left:3px solid ${color};border-radius:10px;padding:12px 18px;color:#e6edf3;font-size:0.83rem;font-family:'Inter',sans-serif;box-shadow:0 8px 32px rgba(0,0,0,0.4);display:flex;align-items:center;gap:10px;min-width:260px;">
            <i class="fa-solid fa-circle-check" style="color:${color};"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:#8b949e;cursor:pointer;padding:0;font-size:1rem;">&times;</button>
        </div>`;
        $('body').append(html);
        setTimeout(() => $('#' + id).fadeOut(400, function() { $(this).remove(); }), 4000);
    }

    // Sortable items
    $('.settings-sortable-items').sortable({
        handle: '.drag-handle',
        placeholder: 'ui-sortable-placeholder',
        axis: 'y',
        stop: function() { showToast('info', 'Order updated locally. Remember to save!'); }
    });

    // Sortable groups sidebar
    $('.settings-sortable-groups').sortable({
        handle: '.drag-handle',
        placeholder: 'ui-sortable-placeholder',
        axis: 'y'
    });

    // Toggle password
    window.togglePassword = function(key) {
        const input = $('#setting-' + key);
        input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
    };

    // Save settings
    $(document).on('click', '.btn-save-settings', function() {
        const btn = $(this);
        const group = btn.data('group');
        const form = $('#form-' + group + '-settings');
        const data = {};

        form.find('.setting-input').each(function() {
            const input = $(this);
            const name = input.attr('name');
            data[name] = input.attr('type') === 'checkbox' ? (input.is(':checked') ? 1 : 0) : input.val();
        });

        btn.html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

        $.ajax({
            url: '{{ route('hub.settings.update') }}',
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: data,
            success: function(res) {
                btn.html('<i class="fa-solid fa-floppy-disk me-2"></i>Save ' + group + ' Changes').prop('disabled', false);
                showToast('success', 'Saved ' + res.updated_count + ' settings successfully!');
            },
            error: function() {
                btn.html('<i class="fa-solid fa-floppy-disk me-2"></i>Save ' + group + ' Changes').prop('disabled', false);
                showToast('danger', 'Error saving settings.');
            }
        });
    });

    // Cache clear custom dialog
    $('#btn-clear-cache').on('click', function() {
        $('<div id="cache-confirm-dialog" title="System Cache Flush"><p>Are you sure you want to flush all system caches? This will reset cognitive buffers.</p></div>').dialog({
            modal: true,
            resizable: false,
            className: 'custom-dialog',
            dialogClass: 'custom-dialog',
            width: 400,
            show: { effect: 'fade', duration: 200 },
            hide: { effect: 'fade', duration: 200 },
            buttons: [
                {
                    text: 'Flush Now',
                    class: 'btn-primary-action',
                    click: function() {
                        const btn = $('#btn-clear-cache');
                        btn.html('<i class="fa-solid fa-spinner fa-spin me-1"></i>Flushing...').prop('disabled', true);
                        const dialogContent = $(this);
                        
                        $.ajax({
                            url: '{{ route('hub.settings.clear-cache') }}',
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            success: function() {
                                btn.html('<i class="fa-solid fa-rotate me-1"></i>Flush').prop('disabled', false);
                                showToast('success', 'System cache cleared successfully!');
                                dialogContent.dialog('close');
                            },
                            error: function() {
                                btn.html('<i class="fa-solid fa-rotate me-1"></i>Flush').prop('disabled', false);
                                showToast('danger', 'Cache clear failed.');
                                dialogContent.dialog('close');
                            }
                        });
                    }
                },
                {
                    text: 'Cancel',
                    click: function() { $(this).dialog('close'); }
                }
            ]
        });
    });

    // Factory purge custom dialog
    $('#btn-factory-purge').on('click', function() {
        $('<div id="purge-confirm-dialog" title="DANGER: Factory Reset">' +
            '<p class="text-danger fw-bold mb-3">Warning: This action is irreversible. Type <span class="text-warning">PURGE</span> to confirm.</p>' +
            '<input type="text" id="purge-confirm-input" class="nx-input" placeholder="Type PURGE here...">' +
          '</div>').dialog({
            modal: true,
            resizable: false,
            dialogClass: 'custom-dialog',
            width: 400,
            show: { effect: 'fade', duration: 200 },
            hide: { effect: 'fade', duration: 200 },
            buttons: [
                {
                    text: 'Purge Everything',
                    class: 'btn-danger-action',
                    click: function() {
                        const input = $('#purge-confirm-input').val();
                        if (input === 'PURGE') {
                            $.ajax({
                                url: '/api/v1/settings/factory-reset',
                                method: 'POST',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                data: { _token: '{{ csrf_token() }}' },
                                success: function() {
                                    showToast('success', 'Factory purge successful!');
                                    window.location.reload();
                                },
                                error: function() {
                                    showToast('danger', 'Failed to factory purge.');
                                }
                            });
                            $(this).dialog('close');
                        } else {
                            showToast('warning', 'Incorrect confirmation text. Please type PURGE.');
                        }
                    }
                },
                {
                    text: 'Cancel',
                    click: function() { $(this).dialog('close'); }
                }
            ]
        });
    });

});
</script>
@endpush
