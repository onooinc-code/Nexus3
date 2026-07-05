<div class="col-md-9">
    <div style="position: relative; height: 100%; width: 100%;">
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;">
            <div class="tab-content h-100" id="v-pills-tabContent">
        @foreach($settings as $group => $items)
            <div class="tab-pane fade h-100 {{ $loop->first ? 'show active' : '' }}" id="v-pills-{{ $group }}" role="tabpanel">
                <div class="h-100 d-flex flex-column" style="gap: 16px;">
                    <div class="flex-grow-1 d-flex flex-column" style="background:rgba(22,27,34,0.6);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.07);border-radius:14px;overflow:hidden; min-height: 0;">
                    {{-- Header --}}
                    <div style="padding:16px 22px;border-bottom:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,0.02);">
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div style="width:36px;height:36px;background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.25);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                <i class="fa-solid fa-sliders" style="color:#58a6ff;font-size:0.9rem;"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;color:#e6edf3;font-family:'Outfit',sans-serif;font-size:1.1rem;line-height:1.2;">{{ ucfirst($group) }} Settings</div>
                                <div style="font-size:0.75rem;color:#8b949e;margin-top:2px;">{{ count($items) }} configuration {{ Str::plural('item', count($items)) }}</div>
                            </div>
                        </div>
                        <span style="font-size:0.75rem;color:#8b949e;font-family:'JetBrains Mono',monospace;background:rgba(255,255,255,0.05);padding:4px 10px;border-radius:6px;"><i class="fa-solid fa-arrows-up-down me-2"></i>Drag to reorder</span>
                    </div>

                    {{-- Body --}}
                    <div style="padding:20px; flex-grow: 1; overflow-y: auto;">
                        <form id="form-{{ $group }}-settings">
                            <div class="row g-3 settings-sortable-items">
                                @foreach($items as $setting)
                                    @include('hubs.settings.setting-item', ['setting' => $setting])
                                @endforeach
                            </div>
                        </form>
                    </div>
                </div>

                @include('hubs.settings.save-panel', ['group' => $group])
            </div>
            </div>
        @endforeach
            </div>
        </div>
    </div>
</div>
