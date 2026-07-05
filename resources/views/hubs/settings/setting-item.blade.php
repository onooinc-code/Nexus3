<div class="col-md-6 setting-item-wrapper mb-3 d-flex">
    <div class="setting-card w-100">
        <div style="display:flex;align-items:flex-start;margin-bottom:20px;">
            <i class="fa-solid fa-grip-vertical drag-handle mt-1 me-3" style="font-size:1rem; cursor: grab;"></i>
            <div class="flex-grow-1 pe-2">
                <div class="setting-desc-label mb-2">{{ $setting->description ?: $setting->key }}</div>
                <span class="setting-key-label d-inline-block">{{ $setting->key }}</span>
            </div>
        </div>
        
        <div style="margin-top:auto;">

        @if($setting->type === 'boolean')
            <div class="nx-switch d-flex align-items-center justify-content-between">
                <span style="font-size:0.78rem;color:#8b949e;">{{ $setting->value ? 'Enabled' : 'Disabled' }}</span>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input setting-input"
                           type="checkbox"
                           role="switch"
                           id="setting-{{ $setting->key }}"
                           name="{{ $setting->key }}"
                           {{ $setting->value ? 'checked' : '' }}>
                </div>
            </div>
        @elseif($setting->type === 'integer')
            <input type="number"
                   class="nx-input setting-input"
                   id="setting-{{ $setting->key }}"
                   name="{{ $setting->key }}"
                   value="{{ $setting->value }}">
        @elseif($setting->is_encrypted)
            <div class="input-group" style="gap:6px;">
                <input type="password"
                       class="nx-input setting-input"
                       id="setting-{{ $setting->key }}"
                       name="{{ $setting->key }}"
                       value="{{ $setting->value }}"
                       readonly
                       style="border-radius:8px !important;">
                <button class="btn btn-sm"
                        type="button"
                        onclick="togglePassword('{{ $setting->key }}')"
                        style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#8b949e;border-radius:8px;padding:0 12px;flex-shrink:0;">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        @else
            <input type="text"
                   class="nx-input setting-input"
                   id="setting-{{ $setting->key }}"
                   name="{{ $setting->key }}"
                   value="{{ $setting->value }}">
        @endif
        </div>
    </div>
</div>
