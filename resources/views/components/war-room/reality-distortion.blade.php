@props(['distortion'])

<div class="eg-card p-4 h-100">
    <div class="eg-section-title"><i class="fa-solid fa-hurricane me-2"></i> REALITY_DISTORTION.exe</div>
    
    <div class="d-flex flex-column gap-4">
        <div>
            <div class="eg-label mb-1">[ CURRENT_ARGUMENT ]</div>
            <div class="text-white" style="font-family: var(--font-ar); font-size: 0.9rem;">
                {{ $distortion['current_argument'] ?? 'N/A' }}
            </div>
        </div>

        <div>
            <div class="eg-label mb-1">[ INVERSION_TACTIC ]</div>
            <div style="color: var(--war-amber); font-family: var(--font-ar); font-size: 0.9rem; font-weight: bold;">
                <i class="fa-solid fa-rotate me-1"></i> {{ $distortion['inversion_tactic'] ?? 'N/A' }}
            </div>
        </div>

        <div class="p-3 mt-auto" style="background: var(--war-green-dim); border: 1px dashed var(--war-green);">
            <div class="eg-label mb-1 text-green">[ EXECUTION_SCRIPT ]</div>
            <div class="text-white" style="font-family: var(--font-ar); font-size: 0.95rem; font-style: italic;">
                {{ $distortion['script'] ?? 'N/A' }}
            </div>
        </div>
    </div>
</div>
