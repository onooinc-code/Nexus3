@props(['subconscious'])

<div class="eg-card p-0 mt-4 position-relative overflow-hidden" id="eg-subconscious-container" style="border: 1px solid var(--studio-red);">
    
    {{-- High-Tech Lock Screen Overlay --}}
    <div id="eg-lock-screen" class="d-flex flex-column align-items-center justify-content-center py-5" style="background: rgba(2,6,23,0.95); position: absolute; inset: 0; z-index: 10; backdrop-filter: blur(10px);">
        <i class="fa-solid fa-biohazard text-red mb-3 animate-pulse" style="font-size: 3.5rem; filter: drop-shadow(0 0 20px rgba(239,68,68,0.6));"></i>
        <h4 class="text-red fw-bold mb-2">القبو الباطني (Classified)</h4>
        <p class="text-muted text-center px-4 mb-4" style="font-size: 0.85rem; max-width: 500px;">
            تحذير: هذا القسم يحتوي على الحقيقة العارية والخبايا النفسية المظلمة. يتطلب تصريح وصول عالي لفك التشفير.
        </p>
        <button id="eg-unlock-btn" type="button" class="btn text-white px-4 py-2" style="background: linear-gradient(135deg, var(--studio-red), #991b1b); border-radius: 50px; font-weight: bold; box-shadow: 0 5px 15px rgba(239,68,68,0.3); border: none; transition: 0.3s;">
            <i class="fa-solid fa-fingerprint me-2"></i> فك التشفير الآن
        </button>
        <div id="eg-unlock-progress" class="mt-3 text-red d-none" style="font-family: monospace; font-size: 0.8rem;">Decrypting Protocol... 0%</div>
    </div>

    {{-- The Real Content --}}
    <div id="eg-unlocked-content" class="p-4" style="opacity: 0.1; filter: blur(5px); pointer-events: none; transition: all 1s ease;">
        <div class="eg-section-title text-red mb-4" style="border-color: rgba(239,68,68,0.2);">
            <i class="fa-solid fa-skull"></i> الحقيقة العارية (The Naked Truth)
        </div>

        <div class="row g-4">
            <div class="col-md-12">
                <div class="p-4 rounded text-center mb-2" style="background: rgba(239,68,68,0.05); border: 1px dashed rgba(239,68,68,0.3);">
                    <div style="font-size: 1.1rem; color: #fca5a5; line-height: 1.8; font-weight: 500;">
                        "{{ $subconscious['the_naked_truth'] ?? 'N/A' }}"
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 h-100" style="border-right: 2px solid var(--studio-red);">
                    <h6 class="text-red mb-2"><i class="fa-solid fa-heart-crack me-1"></i> الجرح النواة (Core Wound)</h6>
                    <p style="font-size: 0.85rem; color: #cbd5e1; line-height: 1.6;">{{ $subconscious['core_wound'] ?? 'N/A' }}</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="p-3 h-100" style="border-right: 2px solid var(--studio-indigo);">
                    <h6 class="text-indigo mb-2"><i class="fa-solid fa-user-ninja me-1"></i> الظل المظلم (Shadow Self)</h6>
                    <p style="font-size: 0.85rem; color: #cbd5e1; line-height: 1.6;">{{ $subconscious['shadow_self'] ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 h-100" style="border-right: 2px solid var(--studio-emerald);">
                    <h6 class="text-emerald mb-2"><i class="fa-solid fa-staff-snake me-1"></i> احتمالية التعافي النرجسي</h6>
                    <p style="font-size: 0.85rem; color: #cbd5e1; line-height: 1.6;">{{ $subconscious['redemption_possibility'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
