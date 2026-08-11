<div class="eg-card p-4 text-center">
    <div class="eg-section-title"><i class="fa-solid fa-person-rays me-2"></i> ANATOMY_TARGET.sys</div>
    
    <div class="position-relative d-inline-block mx-auto mt-3" style="width: 200px; height: 250px;">
        {{-- Very basic abstract human silhouette in SVG --}}
        <svg viewBox="0 0 100 150" width="100%" height="100%" style="opacity: 0.5;">
            <!-- Head -->
            <circle cx="50" cy="20" r="15" fill="none" stroke="var(--war-green)" stroke-width="2" />
            <!-- Brain highlight -->
            <circle cx="50" cy="20" r="8" fill="var(--war-red)" class="animate-pulse" />
            <!-- Body -->
            <path d="M 25 50 C 25 35, 75 35, 75 50 L 75 100 C 75 120, 25 120, 25 100 Z" fill="none" stroke="var(--war-green)" stroke-width="2" />
            <!-- Heart highlight -->
            <circle cx="60" cy="65" r="5" fill="var(--war-amber)" class="animate-pulse" />
        </svg>

        <div class="position-absolute" style="top: 10px; right: 140px; text-align: left; width: 120px;">
            <div class="eg-label text-red border-bottom border-danger pb-1 mb-1">THE_MIND</div>
            <div class="text-white" style="font-family: var(--font-ar); font-size: 0.75rem;">الغرور والاحتياج للسيطرة.</div>
        </div>

        <div class="position-absolute" style="top: 80px; left: 140px; text-align: right; width: 120px;">
            <div class="eg-label text-warning border-bottom border-warning pb-1 mb-1">THE_HEART</div>
            <div class="text-white" style="font-family: var(--font-ar); font-size: 0.75rem;">الخوف الدفين من الهجر.</div>
        </div>
    </div>
</div>
