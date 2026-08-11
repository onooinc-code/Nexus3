<!-- Model Battle -->
<div class="tab-pane fade p-3" id="play-battle">
    <div class="card card-dashboard border-secondary p-4">
        <h5 class="text-light fw-bold mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-bolt text-warning"></i> Multi-Model Battle Arena
        </h5>
        <p class="text-secondary small mb-4">Select two different AI models to execute the exact same prompt concurrently and compare their response speed, accuracy, and output quality side-by-side.</p>

        <form id="battle-form">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-info small fw-bold"><i class="fa-solid fa-robot me-1"></i> Model A (Competitor 1)</label>
                    <select name="model_a_id" id="battle-model-a" class="form-select bg-dark text-light border-secondary" required>
                        <option value="" disabled selected>-- Select Model A --</option>
                        @foreach($models as $model)
                            <option value="{{ $model->id }}">{{ $model->name }} ({{ $model->provider?->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-purple small fw-bold" style="color:#b57edd;"><i class="fa-solid fa-microchip me-1"></i> Model B (Competitor 2)</label>
                    <select name="model_b_id" id="battle-model-b" class="form-select bg-dark text-light border-secondary" required>
                        <option value="" disabled selected>-- Select Model B --</option>
                        @foreach($models as $model)
                            <option value="{{ $model->id }}">{{ $model->name }} ({{ $model->provider?->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label text-secondary small fw-bold">Test Prompt / Instructions</label>
                    <textarea name="message" id="battle-prompt" class="form-control bg-dark text-light border-secondary" rows="3" placeholder="Enter a prompt to test both models (e.g. Write a python script to sort an array using quicksort)..." required></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-warning px-4 fw-bold" id="start-battle-btn">
                        <i class="fa-solid fa-bolt me-2"></i> Launch Battle
                    </button>
                </div>
            </div>
        </form>

        <!-- Battle Results Grid -->
        <div class="row g-4 d-none" id="battle-results-container">
            <div class="col-md-6">
                <div class="card bg-dark border-info p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom border-secondary pb-2">
                        <h6 class="text-info fw-bold mb-0" id="battle-a-title">Model A Output</h6>
                        <span class="badge bg-info bg-opacity-25 text-info" id="battle-a-badge">Model A</span>
                    </div>
                    <div class="text-light font-mono small py-2" id="battle-a-content" style="white-space: pre-wrap; min-height: 150px;">
                        <i class="fa-solid fa-spinner fa-spin me-2 text-info"></i> Running inference...
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-dark border-purple p-3 h-100" style="border-color:#b57edd !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom border-secondary pb-2">
                        <h6 class="fw-bold mb-0" style="color:#b57edd;" id="battle-b-title">Model B Output</h6>
                        <span class="badge bg-opacity-25 text-purple" style="background-color:rgba(181,126,221,0.2);color:#b57edd;" id="battle-b-badge">Model B</span>
                    </div>
                    <div class="text-light font-mono small py-2" id="battle-b-content" style="white-space: pre-wrap; min-height: 150px;">
                        <i class="fa-solid fa-spinner fa-spin me-2" style="color:#b57edd;"></i> Running inference...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#battle-form').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#start-battle-btn');
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Fighting...');
            
            $('#battle-results-container').removeClass('d-none');
            $('#battle-a-content').html('<i class="fa-solid fa-spinner fa-spin me-2 text-info"></i> Running inference...');
            $('#battle-b-content').html('<i class="fa-solid fa-spinner fa-spin me-2" style="color:#b57edd;"></i> Running inference...');

            $.ajax({
                url: '{{ route("hub.models.playground.battle") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt me-2"></i> Launch Battle');
                    if (res.success && res.data) {
                        $('#battle-a-title').text(res.data.model_a.model + ' (' + res.data.model_a.provider + ')');
                        $('#battle-b-title').text(res.data.model_b.model + ' (' + res.data.model_b.provider + ')');
                        
                        $('#battle-a-content').text(res.data.model_a.response || 'No response returned.');
                        $('#battle-b-content').text(res.data.model_b.response || 'No response returned.');
                        if (window.Nexus && window.Nexus.notify) {
                            window.Nexus.notify('Battle executed successfully!', 'success');
                        }
                    } else {
                        if (window.Nexus && window.Nexus.notify) {
                            window.Nexus.notify(res.message || 'Battle execution failed.', 'error');
                        }
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fa-solid fa-bolt me-2"></i> Launch Battle');
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Battle execution failed.';
                    if (window.Nexus && window.Nexus.notify) {
                        window.Nexus.notify(msg, 'error');
                    }
                }
            });
        });
    });
</script>
@endpush
