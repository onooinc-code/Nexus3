<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Linked Devices</h5>
    <div>
        <button class="btn btn-outline-warning btn-sm me-2" id="btn-dummy-device">
            <i class="fa-solid fa-flask"></i> Generate Dummy Device
        </button>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#linkDeviceModal">Link New Device</button>
    </div>
</div>

<div class="row" id="devices-container">
</div>

<div class="modal fade" id="linkDeviceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="fa-solid fa-link text-primary me-2"></i> Manage Devices</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="list-group list-group-flush" id="unassociated-devices-list">
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Device Details Modal -->
<div class="modal fade" id="deviceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-light border-secondary" style="backdrop-filter: blur(10px); background-color: rgba(33, 37, 41, 0.95) !important;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="dd-title"><i class="fa-solid fa-desktop text-primary me-2"></i> Device Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Column: Screenshots -->
                    <div class="col-lg-7 border-end border-secondary">
                        <div class="card bg-black border-secondary mb-3">
                            <img id="dd-latest-screenshot" src="" class="card-img-top" style="height: 400px; object-fit: contain; background: #000;" alt="Latest Screenshot">
                        </div>
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Screenshot Gallery</h6>
                        <div id="dd-gallery" class="d-flex overflow-auto pb-2" style="gap: 10px; height: 100px;">
                            <!-- Thumbnails will go here -->
                        </div>
                    </div>
                    <!-- Right Column: Tabs -->
                    <div class="col-lg-5">
                        <ul class="nav nav-tabs border-secondary mb-3" id="deviceTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active bg-transparent text-light border-secondary border-bottom-0" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link bg-transparent text-muted border-transparent" id="processes-tab" data-bs-toggle="tab" data-bs-target="#processes" type="button" role="tab">Processes</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link bg-transparent text-muted border-transparent" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab">Files</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="deviceTabsContent">
                            <!-- Overview Tab -->
                            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                                <div id="dd-overview-content"></div>
                            </div>
                            <!-- Processes Tab -->
                            <div class="tab-pane fade" id="processes" role="tabpanel">
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-dark table-sm table-hover border-secondary">
                                        <thead><tr><th>Program</th><th class="text-end">RAM (MB)</th></tr></thead>
                                        <tbody id="dd-processes-list"></tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Files Tab -->
                            <div class="tab-pane fade" id="files" role="tabpanel">
                                <div id="dd-files-list" style="max-height: 400px; overflow-y: auto;" class="small"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getFirestore, collection, query, where, onSnapshot, updateDoc, doc, or, addDoc } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-firestore.js";

const firebaseConfig = window.firebaseConfig || {
    apiKey: "{{ env('FIREBASE_API_KEY', '') }}",
    authDomain: "{{ env('FIREBASE_AUTH_DOMAIN', '') }}",
    projectId: "{{ env('FIREBASE_PROJECT_ID', 'nexus-c9155') }}",
    storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET', '') }}",
    messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID', '') }}",
    appId: "{{ env('FIREBASE_APP_ID', '') }}"
};

const app = initializeApp(firebaseConfig);
const db = getFirestore(app);
const contactId = "{{ $contact->id ?? '' }}";
const devicesContainer = document.getElementById('devices-container');
const unassociatedList = document.getElementById('unassociated-devices-list');

// Move modal to body to avoid z-index and backdrop issues when inside a tab-pane
const modalEl = document.getElementById('linkDeviceModal');
if (modalEl && modalEl.parentNode !== document.body) {
    document.body.appendChild(modalEl);
}

const q = collection(db, "devices");

document.getElementById('btn-dummy-device').addEventListener('click', async () => {
    const btn = document.getElementById('btn-dummy-device');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
    try {
        await addDoc(collection(db, "devices"), {
            name: "Dummy Nexus Phone " + Math.floor(Math.random() * 100),
            associated_contact_id: "",
            battery_level: Math.floor(Math.random() * 100),
            latitude: 30.0444 + (Math.random() * 0.1),
            longitude: 31.2357 + (Math.random() * 0.1),
            last_seen: new Date(),
            latest_screenshot_url: "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&q=80"
        });
        if (window.Nexus) Nexus.notify("Dummy device created! Click 'Link New Device' to see it.", "success");
        else alert("Dummy device created! Click 'Link New Device' to see it.");
    } catch (e) {
        if (window.Nexus) Nexus.notify("Error: " + e.message, "error");
        else alert("Error: " + e.message);
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-flask"></i> Generate Dummy Device';
});

window.deviceSnapshotData = {};

onSnapshot(q, (snapshot) => {
    devicesContainer.innerHTML = '';
    unassociatedList.innerHTML = '';
    const now = new Date().getTime() / 1000;

    snapshot.forEach((docSnap) => {
        const data = docSnap.data();
        const id = docSnap.id;
        
        let lastSeenSecs = 0;
        if (data.last_seen) {
            lastSeenSecs = data.last_seen.seconds ? data.last_seen.seconds : (typeof data.last_seen === 'number' ? data.last_seen : Math.floor(new Date(data.last_seen).getTime() / 1000));
        }
        
        const isActive = (now - lastSeenSecs) <= 120;
        const shadowColor = isActive ? 'rgba(25, 135, 84, 0.6)' : 'rgba(108, 117, 125, 0.4)';

        const hardware = data.hardware_snapshot || {};
        const mergedData = { ...data, ...hardware };
        delete mergedData.hardware_snapshot;
        
        // Save to global window for the Details Modal
        window.deviceSnapshotData[id] = mergedData;
        
        const screenshotUrl = data.latest_screenshot_url || data.screenshot_url || data.screenshot || data.image_url || data.image || 
                              hardware.latest_screenshot_url || hardware.screenshot_url || hardware.screenshot || hardware.image_url || hardware.image || 
                              `/api/telemetry/screenshot/${data.device_id || id}?t=${new Date().getTime()}`;

        // Build list item for Link Modal (showing all devices)
        let linkBadge = '';
        let actionBtn = '';
        const isPc = (data.type === 'pc' || (hardware.os_version && hardware.os_version.toLowerCase().includes('windows')));
        const typeIcon = isPc ? '<i class="fa-solid fa-desktop text-primary"></i>' : '<i class="fa-solid fa-mobile-screen-button text-success"></i>';
        
        if (data.associated_contact_id == contactId) {
            linkBadge = '<span class="badge bg-success me-2">Linked to this Contact</span>';
            actionBtn = `<button class="btn btn-sm btn-outline-danger link-action-btn" data-action="unlink" data-id="${id}">Unlink</button>`;
        } else if (data.associated_contact_id) {
            linkBadge = `<span class="badge bg-warning text-dark me-2">Linked to Contact #${data.associated_contact_id}</span>`;
            actionBtn = `<button class="btn btn-sm btn-outline-warning link-action-btn" data-action="link" data-id="${id}">Steal & Re-Link</button>`;
        } else {
            linkBadge = '<span class="badge bg-secondary me-2">Available</span>';
            actionBtn = `<button class="btn btn-sm btn-outline-primary link-action-btn" data-action="link" data-id="${id}">Link Device</button>`;
        }
        
        const listItemHtml = `
            <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between align-items-center">
                <div>
                    ${typeIcon} <strong class="ms-2">${data.name || data.device_name || id}</strong><br>
                    <small class="text-muted">${data.device_id || id}</small>
                </div>
                <div>
                    ${linkBadge}
                    ${actionBtn}
                </div>
            </li>
        `;
        unassociatedList.insertAdjacentHTML('beforeend', listItemHtml);

        // Build card if it belongs to this contact
        if (data.associated_contact_id == contactId) {
            const excludeKeys = ['name', 'device_name', 'associated_contact_id', 'last_seen', 'latest_screenshot_url', 'screenshot_url', 'screenshot', 'image_url', 'image', 'type', 'id', 'latitude', 'longitude', 'battery_level', 'device_id'];
            let propertiesHtml = '<ul class="list-unstyled small mb-0 text-light">';
            let hasProps = false;
            
            if (mergedData.battery_level !== undefined) {
                hasProps = true;
                propertiesHtml += `
                    <li class="mb-2">
                        <strong><i class="fa-solid fa-battery-half text-success me-1"></i> Battery:</strong>
                        <div class="progress d-inline-flex ms-2 align-middle" style="height: 12px; width: 120px;">
                            <div class="progress-bar ${mergedData.battery_level > 20 ? 'bg-success' : 'bg-danger'}" role="progressbar" style="width: ${mergedData.battery_level}%;"></div>
                        </div>
                        <span class="ms-1">${mergedData.battery_level}%</span>
                    </li>
                `;
            }
            
            if (mergedData.latitude && mergedData.longitude) {
                hasProps = true;
                const osmLink = `https://www.openstreetmap.org/?mlat=${mergedData.latitude}&mlon=${mergedData.longitude}#map=18/${mergedData.latitude}/${mergedData.longitude}`;
                propertiesHtml += `<li class="mb-2"><strong><i class="fa-solid fa-location-dot text-danger me-1"></i> Location:</strong> <a href="${osmLink}" target="_blank" class="text-info text-decoration-none" onclick="event.stopPropagation()">View on Map</a></li>`;
            }
            
            function formatValue(val) {
                if (val === null || val === undefined) return '';
                if (typeof val === 'object') {
                    if (val.seconds) return new Date(val.seconds * 1000).toLocaleString();
                    return `<pre class="m-0 mt-1 p-2 bg-black text-success rounded small border border-secondary" style="max-height:100px;overflow:auto;white-space:pre-wrap;font-size:0.7rem;">${JSON.stringify(val, null, 2)}</pre>`;
                }
                return `<span class="text-white-50">${val}</span>`;
            }
            
            for (const [key, value] of Object.entries(mergedData)) {
                if (!excludeKeys.includes(key) && key !== 'running_programs' && key !== 'file_system' && key !== 'storage' && key !== 'ram') {
                    hasProps = true;
                    let formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    
                    let icon = 'fa-solid fa-circle-info text-secondary';
                    if (key.toLowerCase().includes('os') || key.toLowerCase().includes('system')) icon = 'fa-brands fa-windows text-primary';
                    if (key.toLowerCase().includes('cpu') || key.toLowerCase().includes('processor')) icon = 'fa-solid fa-microchip text-warning';
                    if (key.toLowerCase().includes('ip') || key.toLowerCase().includes('net') || key.toLowerCase().includes('mac')) icon = 'fa-solid fa-network-wired text-info';
                    
                    const isComplex = typeof value === 'object';
                    propertiesHtml += `<li class="mb-2 ${!isComplex ? 'text-truncate' : ''}" title="${!isComplex ? value : ''}"><strong><i class="${icon} me-1" style="width:16px;text-align:center;"></i> ${formattedKey}:</strong> ${!isComplex ? formatValue(value) : `<div class="mt-1">${formatValue(value)}</div>`}</li>`;
                }
            }
            propertiesHtml += '</ul>';
            if (!hasProps) propertiesHtml = '<div class="text-muted small">No hardware data available</div>';
            
            const cardHtml = `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 bg-dark border-secondary device-card" data-id="${id}" style="box-shadow: 0 0 15px ${shadowColor}; transition: transform 0.2s, box-shadow 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'" onclick="window.openDeviceDetails('${id}')">
                        <img src="${screenshotUrl}" class="card-img-top border-bottom border-secondary" alt="Screen" style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title text-light">${typeIcon} ${data.name || data.device_name || 'Unknown Device'}</h5>
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-secondary">
                                <span class="badge ${isActive ? 'bg-success' : 'bg-secondary'} me-2">
                                    ${isActive ? 'Online (Active)' : 'Offline'}
                                </span>
                            </div>
                            <div class="device-properties">
                                ${propertiesHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            devicesContainer.insertAdjacentHTML('beforeend', cardHtml);
        }
    });
});

// Event delegation for Link / Unlink actions
unassociatedList.addEventListener('click', async (e) => {
    const btnEl = e.target.closest('.link-action-btn');
    if (!btnEl) return;
    
    const deviceId = btnEl.getAttribute('data-id');
    const action = btnEl.getAttribute('data-action');
    if (!deviceId) return;

    const docRef = doc(db, "devices", deviceId);
    
    const originalText = btnEl.innerHTML;
    btnEl.disabled = true;
    btnEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    
    try {
        const payload = action === 'link' ? { associated_contact_id: contactId } : { associated_contact_id: "" };
        await updateDoc(docRef, payload);
        
        if (window.Nexus) Nexus.notify(`Device ${action === 'link' ? 'linked' : 'unlinked'} successfully!`, "success");
    } catch (error) {
        console.error("Error updating device:", error);
        btnEl.disabled = false;
        btnEl.innerHTML = originalText;
        if (window.Nexus) Nexus.notify("Failed to update device: " + error.message, "error");
        else alert("Failed to update device: " + error.message);
    }
});

// Function to open the heavy device details modal
window.openDeviceDetails = async function(deviceId) {
    const data = window.deviceSnapshotData[deviceId];
    if (!data) return;
    
    const isPc = (data.type === 'pc' || (data.os_version && data.os_version.toLowerCase().includes('windows')));
    const typeIcon = isPc ? '<i class="fa-solid fa-desktop text-primary"></i>' : '<i class="fa-solid fa-mobile-screen-button text-success"></i>';
    
    document.getElementById('dd-title').innerHTML = `${typeIcon} ${data.name || data.device_name || deviceId}`;
    
    const realDeviceId = data.device_id || deviceId;
    document.getElementById('dd-latest-screenshot').src = `/api/telemetry/screenshot/${realDeviceId}?t=${new Date().getTime()}`;
    
    // Fetch gallery
    const galleryContainer = document.getElementById('dd-gallery');
    galleryContainer.innerHTML = '<div class="text-muted small"><i class="fa-solid fa-spinner fa-spin"></i> Loading gallery...</div>';
    
    try {
        const res = await fetch(`/api/telemetry/screenshots/${realDeviceId}`);
        const result = await res.json();
        
        if (result.screenshots && result.screenshots.length > 0) {
            galleryContainer.innerHTML = result.screenshots.map(url => `
                <img src="${url}" class="border border-secondary rounded" style="height: 100px; width: 160px; object-fit: cover; cursor: pointer;" onclick="document.getElementById('dd-latest-screenshot').src='${url}'" title="Click to view">
            `).join('');
        } else {
            galleryContainer.innerHTML = '<div class="text-muted small">No historical screenshots found.</div>';
        }
    } catch (e) {
        galleryContainer.innerHTML = '<div class="text-danger small">Failed to load gallery.</div>';
    }
    
    // Populate Overview
    let overviewHtml = '<div class="row g-3 mb-3">';
    
    // CPU
    if (data.cpu_usage_percent !== undefined) {
        overviewHtml += `
            <div class="col-12">
                <label class="small text-muted mb-1"><i class="fa-solid fa-microchip text-warning"></i> CPU Usage</label>
                <div class="progress" style="height: 20px; background: #333;">
                    <div class="progress-bar ${data.cpu_usage_percent > 85 ? 'bg-danger' : 'bg-primary'}" role="progressbar" style="width: ${data.cpu_usage_percent}%;">${data.cpu_usage_percent}%</div>
                </div>
            </div>`;
    }
    
    // RAM
    if (data.ram && data.ram.total_bytes) {
        const usedGb = (data.ram.used_bytes / 1073741824).toFixed(2);
        const totalGb = (data.ram.total_bytes / 1073741824).toFixed(2);
        const ramPct = Math.round((data.ram.used_bytes / data.ram.total_bytes) * 100);
        overviewHtml += `
            <div class="col-12">
                <label class="small text-muted mb-1"><i class="fa-solid fa-memory text-success"></i> RAM (${usedGb} GB / ${totalGb} GB)</label>
                <div class="progress" style="height: 20px; background: #333;">
                    <div class="progress-bar ${ramPct > 85 ? 'bg-danger' : 'bg-success'}" role="progressbar" style="width: ${ramPct}%;">${ramPct}%</div>
                </div>
            </div>`;
    }
    
    // Storage
    if (data.storage && data.storage.total_bytes) {
        const usedGb = (data.storage.used_bytes / 1073741824).toFixed(2);
        const totalGb = (data.storage.total_bytes / 1073741824).toFixed(2);
        const stoPct = Math.round((data.storage.used_bytes / data.storage.total_bytes) * 100);
        overviewHtml += `
            <div class="col-12">
                <label class="small text-muted mb-1"><i class="fa-solid fa-hard-drive text-info"></i> Storage (${usedGb} GB / ${totalGb} GB)</label>
                <div class="progress" style="height: 20px; background: #333;">
                    <div class="progress-bar ${stoPct > 85 ? 'bg-danger' : 'bg-info'}" role="progressbar" style="width: ${stoPct}%;">${stoPct}%</div>
                </div>
            </div>`;
    }
    
    overviewHtml += '</div>';
    
    // Basic Data
    overviewHtml += '<ul class="list-group list-group-flush border border-secondary rounded">';
    for (const [k, v] of Object.entries(data)) {
        if (!['name', 'device_name', 'associated_contact_id', 'screenshot', 'image', 'latest_screenshot_url', 'type', 'id', 'device_id', 'cpu_usage_percent', 'ram', 'storage', 'running_programs', 'file_system'].includes(k)) {
            if (typeof v !== 'object') {
                overviewHtml += `<li class="list-group-item bg-transparent text-light border-secondary"><strong>${k.replace(/_/g, ' ').toUpperCase()}:</strong> <span class="text-muted float-end">${v}</span></li>`;
            }
        }
    }
    overviewHtml += '</ul>';
    document.getElementById('dd-overview-content').innerHTML = overviewHtml;
    
    // Populate Processes
    const procList = document.getElementById('dd-processes-list');
    if (data.running_programs && Array.isArray(data.running_programs)) {
        procList.innerHTML = data.running_programs.map(p => `
            <tr>
                <td><i class="fa-solid fa-window-maximize text-secondary me-2"></i> ${p.name}</td>
                <td class="text-end text-muted">${p.memory_mb || '-'}</td>
            </tr>
        `).join('');
    } else {
        procList.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No process data</td></tr>';
    }
    
    // Populate Filesystem safely
    const fileList = document.getElementById('dd-files-list');
    if (data.file_system) {
        try {
            let fileHtml = '<ul class="list-unstyled ms-2">';
            const renderTree = (node, name) => {
                const isFolder = node.type === 'folder' || node.type === 'dir';
                let html = `<li><i class="fa-solid ${isFolder ? 'fa-folder text-warning' : 'fa-file text-light'} me-2"></i> ${name}`;
                if (node.children) {
                    html += '<ul class="list-unstyled ms-4 border-start border-secondary ps-2">';
                    if (Array.isArray(node.children)) {
                        node.children.forEach(c => { html += renderTree(c, c.name); });
                    } else if (typeof node.children === 'object') {
                        for (const [ck, cv] of Object.entries(node.children)) {
                            html += renderTree(cv, ck);
                        }
                    }
                    html += '</ul>';
                }
                html += '</li>';
                return html;
            };
            for (const [k, v] of Object.entries(data.file_system)) {
                fileHtml += renderTree(v, k);
            }
            fileHtml += '</ul>';
            fileList.innerHTML = fileHtml;
        } catch(e) {
            console.error("Filesystem render error", e);
            fileList.innerHTML = '<div class="text-danger small p-3">Error rendering filesystem data.</div>';
        }
    } else {
        fileList.innerHTML = '<div class="text-muted text-center p-3">No filesystem data available.</div>';
    }
    
    if (window.jQuery) {
        $('#deviceDetailsModal').modal('show');
    } else {
        let m = bootstrap.Modal.getInstance(document.getElementById('deviceDetailsModal')) || new bootstrap.Modal(document.getElementById('deviceDetailsModal'));
        m.show();
    }
};

// Handle manual tabs for absolute reliability
document.addEventListener('click', function(e) {
    if (e.target.matches('#deviceTabs .nav-link')) {
        e.preventDefault();
        // Remove active class from all tabs
        document.querySelectorAll('#deviceTabs .nav-link').forEach(t => {
            t.classList.remove('active', 'text-light', 'border-bottom-0');
            t.classList.add('text-muted');
        });
        // Hide all panes
        document.querySelectorAll('#deviceTabsContent .tab-pane').forEach(p => p.classList.remove('show', 'active'));
        
        // Activate clicked tab
        e.target.classList.add('active', 'text-light', 'border-bottom-0');
        e.target.classList.remove('text-muted');
        const targetPane = document.querySelector(e.target.getAttribute('data-bs-target'));
        if (targetPane) targetPane.classList.add('show', 'active');
    }
});

// Event delegation for gallery image swap
document.getElementById('dd-gallery').addEventListener('click', function(e) {
    if (e.target.tagName === 'IMG') {
        document.getElementById('dd-latest-screenshot').src = e.target.src;
    }
});
</script>
