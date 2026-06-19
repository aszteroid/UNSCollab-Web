// Dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    checkSession();
    initializeDashboard();
    initializeCharts();
});

// Check if user is logged in
function checkSession() {
    if (!window.userData || (!window.userData.id && !window.userData.type_id)) {
        window.location.href = '/';
        return;
    }

    const greetName    = document.getElementById('greetName');
    const topbarAvatar = document.getElementById('topbarAvatar');
    const setEmail     = document.getElementById('set-email');

    if (greetName) greetName.textContent = window.userData.name;
    if (setEmail)  setEmail.value        = window.userData.email;

    if (topbarAvatar) {
        const initials = (window.userData.name || '--').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        topbarAvatar.textContent = initials;
    }
}

// Initialize dashboard
async function initializeDashboard() {
    try {
        const response = await fetch('/api/dashboard', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();

        if (!data.success) return;

        const today = new Date();
        const lowonganAktif = (data.lowongan || []).filter(l =>
            l.approval_status === 'approved' &&
            (!l.deadline || new Date(l.deadline) >= today)
        ).length;

        setEl('sv1', lowonganAktif);
        setEl('sv2', data.stats.total_pelamar);
        setEl('sv3', data.stats.pending_lowongan);
        setEl('sv4', data.stats.diterima);

        setEl('lowonganBadge', data.lowongan?.length || 0);
        setEl('pelamarBadge',  data.stats.total_pelamar);

        renderLowongan(data.lowongan);
        renderPelamar(data.pelamar);
        renderBarChart(data.pelamar);
        renderProgressStatus(data.pelamar);
        loadActivitiesTimeline();
        loadNotifDropdown(data.pelamar, data.lowongan);
        loadProfile();

    } catch (error) {
        console.error('Error fetch dashboard:', error);
    }
}

function setEl(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val ?? '0';
}

function renderBarChart(pelamar) {
    const container = document.getElementById('bar-chart');
    if (!container) return;

    const countMap = {};
    (pelamar || []).forEach(p => {
        const posisi = p.posisi || 'Lainnya';
        countMap[posisi] = (countMap[posisi] || 0) + 1;
    });

    const labels = Object.keys(countMap);
    const values = Object.values(countMap);

    if (labels.length === 0) {
        container.innerHTML = '<p style="text-align:center;color:var(--muted);font-size:13px;padding:40px 0">Belum ada data pelamar</p>';
        return;
    }

    const maxVal = Math.max(...values);
    container.innerHTML = labels.map((label, i) => {
        const heightPct = maxVal > 0 ? Math.round((values[i] / maxVal) * 100) : 0;
        return `
        <div class="bar-item">
            <div class="bar-fill" style="height:${heightPct}%;background:var(--brand)"></div>
            <div class="bar-lbl" title="${label}">${label.length > 10 ? label.substring(0,10)+'…' : label}</div>
        </div>`;
    }).join('');
}

function renderProgressStatus(pelamar) {
    const container = document.getElementById('progress-container');
    if (!container) return;

    const total = (pelamar || []).length;
    if (total === 0) {
        container.innerHTML = '<p style="text-align:center;color:var(--muted);font-size:13px;padding:20px 0">Belum ada pelamar</p>';
        return;
    }

    const counts = { pending: 0, reviewed: 0, accepted: 0, rejected: 0 };
    pelamar.forEach(p => { if (counts[p.application_status] !== undefined) counts[p.application_status]++; });

    const items = [
        { label: 'Menunggu',  key: 'pending',  color: '#F59E0B' },
        { label: 'Direview',  key: 'reviewed', color: '#6366F1' },
        { label: 'Diterima',  key: 'accepted', color: '#16A34A' },
        { label: 'Ditolak',   key: 'rejected', color: '#DC2626' },
    ];

    container.innerHTML = items.map(item => {
        const pct = total > 0 ? Math.round((counts[item.key] / total) * 100) : 0;
        return `
        <div class="prog-row">
            <div class="prog-top">
                <span style="font-size:12px;font-weight:500">${item.label}</span>
                <span style="font-size:12px;color:var(--muted)">${counts[item.key]} (${pct}%)</span>
            </div>
            <div class="prog-bar">
                <div class="prog-fill" style="width:${pct}%;background:${item.color}"></div>
            </div>
        </div>`;
    }).join('');
}

// Upload logo dilakukan via applyCrop() menggunakan crop system

async function loadActivitiesTimeline() {
    const container = document.getElementById('activity-timeline');
    if (!container) return;

    try {
        const response = await fetch('/api/activities');
        const data = await response.json();

        if (!data.success || data.data.length === 0) {
            container.innerHTML = '<p style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0">Belum ada aktivitas</p>';
            return;
        }

        container.innerHTML = data.data.map(a => {
            const date = a.created_at
                ? new Date(a.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                : '-';
            return `
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="mini-avatar" style="background:#EEF3FF;color:var(--brand);flex-shrink:0">
                    <i class="bi bi-activity"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600">${a.action}</div>
                    <div style="font-size:11px;color:var(--muted)">${date}</div>
                </div>
            </div>`;
        }).join('');
    } catch (e) {
        console.error('loadActivitiesTimeline error:', e);
    }
}

let _allLowongan = [];

function renderLowongan(lowongan) {
    _allLowongan = lowongan || [];
    _renderLowonganGrid(_allLowongan);
    _updateLowonganCounts(_allLowongan);
}

function _updateLowonganCounts(lowongan) {
    const counts = { all: lowongan.length, active: 0, pending: 0, closed: 0 };
    const today  = new Date();

    lowongan.forEach(l => {
        if (l.approval_status === 'pending') counts.pending++;
        else if (l.approval_status === 'approved') {
            const deadline = l.deadline ? new Date(l.deadline) : null;
            if (deadline && deadline < today) counts.closed++;
            else counts.active++;
        } else if (l.approval_status === 'rejected') counts.closed++;
    });

    setEl('count-all',     counts.all);
    setEl('count-aktif',   counts.active);
    setEl('count-pending', counts.pending);
    setEl('count-ditutup', counts.closed);

    const banner       = document.getElementById('pending-banner');
    const pendingCount = document.getElementById('pending-count');
    if (banner)       banner.style.display      = counts.pending > 0 ? 'flex' : 'none';
    if (pendingCount) pendingCount.textContent   = counts.pending;
}

function filterLw(filter, el) {
    document.querySelectorAll('.nav-pill-tab').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');

    const today = new Date();
    let filtered = _allLowongan;

    if (filter === 'active') {
        filtered = _allLowongan.filter(l => {
            if (l.approval_status !== 'approved') return false;
            const deadline = l.deadline ? new Date(l.deadline) : null;
            return !deadline || deadline >= today;
        });
    } else if (filter === 'pending') {
        filtered = _allLowongan.filter(l => l.approval_status === 'pending');
    } else if (filter === 'closed') {
        filtered = _allLowongan.filter(l => {
            if (l.approval_status === 'rejected') return true;
            if (l.approval_status === 'approved') {
                const deadline = l.deadline ? new Date(l.deadline) : null;
                return deadline && deadline < today;
            }
            return false;
        });
    }

    _renderLowonganGrid(filtered);
}

function _renderLowonganGrid(lowongan) {
    const grid  = document.getElementById('lw-grid');
    const empty = document.getElementById('empty-lowongan');
    if (!grid) return;

    if (!lowongan || lowongan.length === 0) {
        if (empty) empty.style.display = 'block';
        grid.innerHTML = '';
        return;
    }

    if (empty) empty.style.display = 'none';

    const today = new Date();
    grid.innerHTML = lowongan.map(l => {
        const isPending  = l.approval_status === 'pending';
        const isApproved = l.approval_status === 'approved';
        const isRejected = l.approval_status === 'rejected';
        const deadline   = l.deadline ? new Date(l.deadline) : null;
        const isClosed   = isApproved && deadline && deadline < today;

        let statusColor, statusLabel;
        if (isPending)       { statusColor = 'pill-yellow'; statusLabel = 'Menunggu Review'; }
        else if (isRejected) { statusColor = 'pill-red';    statusLabel = 'Ditolak'; }
        else if (isClosed)   { statusColor = 'pill-red';    statusLabel = 'Ditutup'; }
        else                 { statusColor = 'pill-green';  statusLabel = 'Aktif'; }

        const clickHandler = isApproved && !isClosed
            ? `showPelamarByLowongan('${l.id_internship}', '${l.title.replace(/'/g, "\\'")}')`
            : '';

        return `
        <div class="col-md-6 col-lg-4">
          <div class="lw-card ${isPending || isClosed || isRejected ? 'pending-card' : ''}"
               onclick="${clickHandler}" style="${!clickHandler ? 'cursor:default' : ''}">
            ${isPending  ? `<div class="pending-banner"><i class="bi bi-clock"></i> Sedang direview admin</div>` : ''}
            ${isRejected ? `<div class="pending-banner" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B"><i class="bi bi-x-circle"></i> Ditolak admin</div>` : ''}
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="lw-icon" style="background:#EEF3FF;color:var(--brand)"><i class="bi bi-briefcase"></i></div>
              <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${l.title}</div>
                <div style="font-size:11px;color:var(--muted)">${l.location || '-'}</div>
              </div>
            </div>
            <div class="d-flex flex-wrap gap-1 mb-3">
              <span class="float-label">${l.work_mode || '-'}</span>
              <span class="float-label">${l.payment_status || '-'}</span>
              <span class="float-label">Kuota: ${l.quota || '-'}</span>
            </div>
            <div class="lw-footer">
              <span class="pill ${statusColor}">${statusLabel}</span>
              <span style="font-size:11px;color:var(--muted)">Deadline: ${deadline ? deadline.toLocaleDateString('id-ID') : '-'}</span>
            </div>
            ${isPending ? `
            <div class="d-flex gap-2 mt-3" onclick="event.stopPropagation()">
              <button class="btn-outline btn-sm w-100" onclick="showEditLowongan(${JSON.stringify(l).replace(/"/g, '&quot;')})">
                <i class="bi bi-pencil"></i> Edit
              </button>
              <button class="btn-danger-soft btn-sm w-100" onclick="handleDeleteLowongan('${l.id_internship}', '${l.title.replace(/'/g, "\\'")}')">
                <i class="bi bi-trash3"></i> Hapus
              </button>
            </div>` : ''}
          </div>
        </div>`;
    }).join('');
}

function showPelamarByLowongan(idInternship, title) {
    const subtitle = document.getElementById('pelamar-subtitle');
    if (subtitle) subtitle.innerHTML = `Lowongan: <strong>${title}</strong>`;

    const filterPosisi = document.getElementById('filter-posisi');
    if (filterPosisi) {
        Array.from(filterPosisi.options).forEach(opt => {
            if (opt.value === idInternship) opt.selected = true;
        });
    }

    showPage('pelamar', null);
    filterPelamar();
}

let _allPelamar = [];

function renderPelamar(pelamar) {
    _allPelamar = pelamar || [];

    const filterPosisi = document.getElementById('filter-posisi');
    if (filterPosisi) {
        const posisiSet = [...new Set(_allPelamar.map(p => JSON.stringify({ id: p.id_internship, title: p.posisi })))];
        filterPosisi.innerHTML = '<option value="">Semua Posisi</option>' +
            posisiSet.map(s => {
                const obj = JSON.parse(s);
                return `<option value="${obj.id}">${obj.title}</option>`;
            }).join('');
        filterPosisi.onchange = filterPelamar;
    }

    const filterStatus = document.getElementById('filter-status');
    if (filterStatus) filterStatus.onchange = filterPelamar;

    const searchInput = document.getElementById('search-pelamar');
    if (searchInput) searchInput.oninput = filterPelamar;

    setEl('total-pelamar', _allPelamar.length);
    _renderPelamarTable(_allPelamar);
}

function filterPelamar() {
    const posisi = document.getElementById('filter-posisi')?.value || '';
    const status = document.getElementById('filter-status')?.value || '';
    const search = (document.getElementById('search-pelamar')?.value || '').toLowerCase();

    const filtered = _allPelamar.filter(p => {
        const matchPosisi = !posisi || p.id_internship === posisi;
        const matchStatus = !status || p.application_status === status;
        const matchSearch = !search || (p.full_name || '').toLowerCase().includes(search);
        return matchPosisi && matchStatus && matchSearch;
    });

    setEl('total-pelamar', filtered.length);
    _renderPelamarTable(filtered);
}

function _renderPelamarTable(pelamar) {
    const tbody          = document.getElementById('pelamar-tbody');
    const empty          = document.getElementById('empty-pelamar');
    const tableContainer = document.getElementById('pelamar-table-container');

    if (!tbody) return;

    if (!pelamar || pelamar.length === 0) {
        if (empty) empty.style.display = 'flex';
        if (tableContainer) tableContainer.style.display = 'none';
        return;
    }

    if (empty) empty.style.display = 'none';
    if (tableContainer) tableContainer.style.display = 'block';

    tbody.innerHTML = pelamar.map(p => {
        const statusColor = { pending:'pill-yellow', reviewed:'pill-purple', accepted:'pill-green', rejected:'pill-red' }[p.application_status] || 'pill-blue';
        const statusLabel = { pending:'Menunggu', reviewed:'Direview', accepted:'Diterima', rejected:'Ditolak' }[p.application_status] || p.application_status;
        const initials    = (p.full_name || '--').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        // FIX: format tanggal — handle null dan timezone dengan benar
        const date        = p.apply_date ? new Date(p.apply_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'numeric', year: 'numeric' }) : '-';

        return `
        <tr onclick="showDetailPelamar(${JSON.stringify(p).replace(/"/g, '&quot;')})">
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="mini-avatar" style="background:#EEF3FF;color:var(--brand)">${initials}</div>
              <div>
                <div style="font-weight:600">${p.full_name || '-'}</div>
                <div style="font-size:11px;color:var(--muted)">${p.email || '-'}</div>
              </div>
            </div>
          </td>
          <td>${p.posisi || '-'}</td>
          <td>${p.major || '-'}</td>
          <td>${date}</td>
          <td><span class="pill ${statusColor}">${statusLabel}</span></td>
          <td>
            <button class="btn-brand btn-sm" onclick="event.stopPropagation(); showDetailPelamar(${JSON.stringify(p).replace(/"/g, '&quot;')})">
              <i class="bi bi-eye"></i>
            </button>
          </td>
        </tr>`;
    }).join('');
}

function showDetailPelamar(p) {
    const initials = (p.full_name || '--').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

    const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    const setHtml = (id, val) => { const el = document.getElementById(id); if (el) el.innerHTML = val; };

    setTxt('det-avatar',  initials);
    setTxt('det-name',    p.full_name || '-');
    setTxt('det-posisi',  p.posisi    || '-');
    setTxt('det-jurusan', p.major     || '-');
    setTxt('det-email',   p.email     || '-');

    // Format tanggal
    setTxt('det-date', p.apply_date
        ? new Date(p.apply_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
        : '-');

    // Angkatan dari NIM (digit 3-4 = tahun masuk, contoh M0521001 → 2021)
    const angkatan = p.nim ? '20' + p.nim.replace(/[^0-9]/g, '').substring(2, 4) : 'N/A';
    setTxt('det-angkatan', angkatan);
    setTxt('det-phone',    p.contact || p.phone || 'N/A');

    // Data students
    setTxt('det-bio',        p.bio          || 'Belum ada bio.');
    setTxt('det-experience', p.experience   || 'Belum ada pengalaman.');
    setTxt('det-surat',      p.cover_letter || 'Tidak ada surat lamaran.');

    // Portofolio
    const portoEl = document.getElementById('det-portofolio');
    if (portoEl) {
        portoEl.innerHTML = p.portofolio
            ? `<a href="${p.portofolio}" target="_blank" rel="noopener" style="color:var(--brand)">${p.portofolio}</a>`
            : 'Belum ada portofolio.';
    }

    // Skill tags
    const skillEl = document.getElementById('det-skills');
    if (skillEl) {
        skillEl.innerHTML = p.skill
            ? p.skill.split(',').map(s => `<span class="float-label" style="margin-bottom:4px">${s.trim()}</span>`).join('')
            : 'Belum ada keahlian.';
    }

    // Dokumen CV
    setHtml('det-documents', p.cv
        ? `<a href="/${p.cv}" target="_blank" class="btn-outline btn-sm"><i class="bi bi-file-earmark-pdf"></i> Lihat CV</a>`
        : '<span style="color:var(--muted);font-size:13px">Tidak ada dokumen.</span>');

    // Hidden inputs untuk update status
    const elStudent    = document.getElementById('det-id-student');
    const elInternship = document.getElementById('det-id-internship');
    if (elStudent)    elStudent.value    = p.id_student;
    if (elInternship) elInternship.value = p.id_internship;

    // Status badge
    const statusColor = { pending:'pill-yellow', reviewed:'pill-purple', accepted:'pill-green', rejected:'pill-red' }[p.application_status] || 'pill-blue';
    const statusLabel = { pending:'Menunggu', reviewed:'Direview', accepted:'Diterima', rejected:'Ditolak' }[p.application_status] || p.application_status;
    setHtml('det-status-wrap', `<span class="pill ${statusColor}">${statusLabel}</span>`);

    // Tombol aksi dinamis
    const aksiWrap = document.getElementById('det-aksi-wrap');
    if (aksiWrap) {
        if (p.application_status === 'accepted') {
            aksiWrap.innerHTML = `
                <button class="btn-danger-soft" onclick="updatePelamarStatus('rejected')"><i class="bi bi-x-lg"></i> Tolak</button>
                <button class="btn-outline btn-sm" onclick="updatePelamarStatus('pending')" style="opacity:.7"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>`;
        } else if (p.application_status === 'rejected') {
            aksiWrap.innerHTML = `
                <button class="btn-success-soft" onclick="updatePelamarStatus('accepted')"><i class="bi bi-check-lg"></i> Terima</button>
                <button class="btn-outline btn-sm" onclick="updatePelamarStatus('pending')" style="opacity:.7"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>`;
        } else if (p.application_status === 'reviewed') {
            aksiWrap.innerHTML = `
                <button class="btn-success-soft" onclick="updatePelamarStatus('accepted')"><i class="bi bi-check-lg"></i> Terima</button>
                <button class="btn-danger-soft" onclick="updatePelamarStatus('rejected')"><i class="bi bi-x-lg"></i> Tolak</button>`;
        } else {
            aksiWrap.innerHTML = `
                <button class="btn-outline btn-sm" onclick="updatePelamarStatus('reviewed')"><i class="bi bi-eye"></i> Tandai Direview</button>
                <button class="btn-success-soft" onclick="updatePelamarStatus('accepted')"><i class="bi bi-check-lg"></i> Terima</button>
                <button class="btn-danger-soft" onclick="updatePelamarStatus('rejected')"><i class="bi bi-x-lg"></i> Tolak</button>`;
        }
    }

    showPage('detail', null);
}

// ── CROP SYSTEM ────────────────────────────────────────
let cropState = { scale: 1, ox: 0, oy: 0, isDragging: false, lastX: 0, lastY: 0 };
const CROP_SIZE = 360;

function openCropModal(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        showToast('✗ Ukuran file maksimal 2MB');
        event.target.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = e => initCrop(e.target.result);
    reader.readAsDataURL(file);
}

function initCrop(imageSrc) {
    const container = document.getElementById('crop-container');
    const img       = document.getElementById('crop-img');
    if (!container || !img) return;

    cropState = { scale: 1, ox: 0, oy: 0, isDragging: false, lastX: 0, lastY: 0 };

    img.onload = () => {
        const fitScale = Math.max(CROP_SIZE / img.naturalWidth, CROP_SIZE / img.naturalHeight);
        cropState.scale = fitScale;
        document.getElementById('crop-zoom').min   = Math.round(fitScale * 100);
        document.getElementById('crop-zoom').max   = Math.round(fitScale * 100 * 4);
        document.getElementById('crop-zoom').value = Math.round(fitScale * 100);
        _applyCropTransform();
    };
    img.src = imageSrc;
    container.style.display = 'block';

    const viewport = container.querySelector('.crop-viewport');

    viewport.onmousedown = (e) => {
        cropState.isDragging = true;
        cropState.lastX = e.clientX;
        cropState.lastY = e.clientY;
        viewport.style.cursor = 'grabbing';
        e.preventDefault();
    };
    window.onmousemove = (e) => {
        if (!cropState.isDragging) return;
        const dx = (e.clientX - cropState.lastX) / cropState.scale;
        const dy = (e.clientY - cropState.lastY) / cropState.scale;
        cropState.ox -= dx;
        cropState.oy -= dy;
        cropState.lastX = e.clientX;
        cropState.lastY = e.clientY;
        _applyCropTransform();
    };
    window.onmouseup = () => {
        cropState.isDragging = false;
        viewport.style.cursor = 'grab';
    };

    let lastTouchDist = null;
    viewport.ontouchstart = (e) => {
        if (e.touches.length === 1) {
            cropState.isDragging = true;
            cropState.lastX = e.touches[0].clientX;
            cropState.lastY = e.touches[0].clientY;
        }
        if (e.touches.length === 2) {
            lastTouchDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
        }
        e.preventDefault();
    };
    viewport.ontouchmove = (e) => {
        if (e.touches.length === 1 && cropState.isDragging) {
            const dx = (e.touches[0].clientX - cropState.lastX) / cropState.scale;
            const dy = (e.touches[0].clientY - cropState.lastY) / cropState.scale;
            cropState.ox -= dx;
            cropState.oy -= dy;
            cropState.lastX = e.touches[0].clientX;
            cropState.lastY = e.touches[0].clientY;
            _applyCropTransform();
        }
        if (e.touches.length === 2 && lastTouchDist !== null) {
            const dist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            const ratio  = dist / lastTouchDist;
            const zoomEl = document.getElementById('crop-zoom');
            const newScale = Math.min(
                Math.max(cropState.scale * ratio, Number(zoomEl.min) / 100),
                Number(zoomEl.max) / 100
            );
            cropState.scale = newScale;
            zoomEl.value    = Math.round(newScale * 100);
            lastTouchDist   = dist;
            _applyCropTransform();
        }
        e.preventDefault();
    };
    viewport.ontouchend = () => {
        cropState.isDragging = false;
        lastTouchDist = null;
    };

    viewport.onwheel = (e) => {
        e.preventDefault();
        const zoomEl = document.getElementById('crop-zoom');
        const delta  = e.deltaY < 0 ? 5 : -5;
        const newVal = Math.min(Math.max(Number(zoomEl.value) + delta, Number(zoomEl.min)), Number(zoomEl.max));
        zoomEl.value = newVal;
        handleCropZoom(newVal);
    };
}

function handleCropZoom(value) {
    cropState.scale = value / 100;
    _applyCropTransform();
}

function _applyCropTransform() {
    const img = document.getElementById('crop-img');
    if (!img) return;
    const cx = CROP_SIZE / 2 - cropState.ox * cropState.scale;
    const cy = CROP_SIZE / 2 - cropState.oy * cropState.scale;
    img.style.transform       = 'none';
    img.style.position        = 'absolute';
    img.style.transformOrigin = '0 0';
    img.style.width           = img.naturalWidth  * cropState.scale + 'px';
    img.style.height          = img.naturalHeight * cropState.scale + 'px';
    img.style.left            = (cx - img.naturalWidth  * cropState.scale / 2) + 'px';
    img.style.top             = (cy - img.naturalHeight * cropState.scale / 2) + 'px';
}

function showToast(msg) {
    const toast    = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');
    if (!toast || !toastMsg) return;
    toastMsg.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

function initializeCharts() {
    // Placeholder — data diisi oleh renderBarChart() setelah fetch
}

function loadNotifDropdown(pelamar, lowongan) {
    const list = document.getElementById('notif-list');
    const dot  = document.getElementById('ndot');
    if (!list) return;

    const notifs = [];

    (pelamar || []).filter(p => p.application_status === 'pending').slice(0, 5).forEach(p => {
        notifs.push({
            color: '#3B82F6',
            text:  `<strong>${p.full_name || 'Seseorang'}</strong> melamar posisi <strong>${p.posisi || '-'}</strong>`,
            time:  p.apply_date
                ? new Date(p.apply_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
                : 'Baru saja',
        });
    });

    (lowongan || []).filter(l => l.approval_status === 'pending').slice(0, 3).forEach(l => {
        notifs.push({ color: '#F59E0B', text: `Lowongan <strong>${l.title || '-'}</strong> sedang menunggu verifikasi admin`, time: 'Pending' });
    });

    (lowongan || []).filter(l => l.approval_status === 'rejected').slice(0, 3).forEach(l => {
        notifs.push({ color: '#DC2626', text: `Lowongan <strong>${l.title || '-'}</strong> ditolak oleh admin`, time: 'Ditolak' });
    });

    if (notifs.length === 0) {
        list.innerHTML = `
            <div style="padding:20px 16px;text-align:center;font-size:13px;color:var(--muted)">
                <i class="bi bi-bell-slash" style="font-size:24px;display:block;margin-bottom:6px"></i>
                Tidak ada notifikasi baru
            </div>`;
        if (dot) dot.style.display = 'none';
        return;
    }

    list.innerHTML = notifs.map(n => `
        <div class="notif-item">
            <div class="notif-ico" style="background:${n.color}"></div>
            <div>
                <div class="notif-text">${n.text}</div>
                <div class="notif-time">${n.time}</div>
            </div>
        </div>`).join('');

    if (dot) dot.style.display = 'block';
}

function handleDocumentUpload(event) {
    const file    = event.target.files[0];
    const docList = document.getElementById('doc-list');
    if (!docList || !file) return;

    const size = (file.size / 1024 / 1024).toFixed(2);
    docList.innerHTML = `
        <div class="d-flex align-items-center gap-2 p-2" style="background:#f8f9fa;border-radius:6px;font-size:13px">
            <i class="bi bi-file-earmark-pdf" style="color:#dc3545"></i>
            <span style="flex:1">${file.name}</span>
            <span style="color:var(--muted)">${size} MB</span>
            <i class="bi bi-x-circle" style="cursor:pointer;color:#dc3545" onclick="this.parentElement.remove(); document.getElementById('doc-input').value=''"></i>
        </div>`;
}

function handleImagePreview(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        showToast('✗ Ukuran gambar maksimal 2MB');
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const previewWrap = document.getElementById('image-preview-wrap');
        const previewImg  = document.getElementById('image-preview');
        if (previewImg)  previewImg.src = e.target.result;
        if (previewWrap) previewWrap.style.display = 'block';
        document.getElementById('image-upload-area').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function clearImagePreview() {
    const previewWrap = document.getElementById('image-preview-wrap');
    const uploadArea  = document.getElementById('image-upload-area');
    const input       = document.getElementById('image-input');
    if (previewWrap) previewWrap.style.display = 'none';
    if (uploadArea)  uploadArea.style.display  = 'block';
    if (input)       input.value               = '';
}

function switchProfTab(tab, el) {
    document.querySelectorAll('.prof-tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.prof-tab').forEach(t => {
        t.style.borderBottom = 'none';
        t.style.color = 'inherit';
    });
    const target = document.getElementById('tab-' + tab);
    if (target) target.style.display = 'block';
    if (el) {
        el.style.borderBottom = '3px solid var(--brand)';
        el.style.color = 'var(--brand)';
    }
}

async function handleSubmitLowongan() {
    const form = document.getElementById('form-lowongan');

    const title        = form.querySelector('[name=title]').value.trim();
    const description  = form.querySelector('[name=description]').value.trim();
    const requirements = form.querySelector('[name=requirements]').value.trim();
    const imageFile    = document.getElementById('image-input')?.files[0];
    const docFile      = document.getElementById('doc-input')?.files[0];

    if (!title || !description || !requirements || !docFile) {
        showToast('Judul, deskripsi, kualifikasi, dan dokumen pendukung wajib diisi!');
        return;
    }

    const formData = new FormData();
    formData.append('title',          title);
    formData.append('description',    description);
    formData.append('requirements',   requirements);
    formData.append('benefit',        form.querySelector('[name=benefits]').value.trim());
    formData.append('location',       form.querySelector('[name=location]').value);
    formData.append('work_mode',      form.querySelector('[name=work_mode]').value);
    formData.append('payment_status', form.querySelector('[name=payment_status]').value);
    formData.append('quota',          form.querySelector('[name=quota]').value);
    formData.append('duration',       form.querySelector('[name=duration]').value.trim());
    formData.append('deadline',       form.querySelector('[name=deadline]').value);

    if (imageFile) formData.append('image', imageFile);
    formData.append('supporting_document', docFile);

    try {
        const response = await fetch('/api/internship/store', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            showToast('✓ ' + data.message);
            form.reset();
            clearImagePreview();
            document.getElementById('doc-list').innerHTML = '';
            showPage('lowongan', null);
            initializeDashboard();
        } else {
            showToast('✗ ' + data.message);
        }
    } catch (error) {
        showToast('✗ Terjadi kesalahan, coba lagi.');
    }
}

async function loadProfile() {
    try {
        const response = await fetch('/api/profile');
        const data     = await response.json();
        if (data.success) {
            const c = data.data;

            const elName     = document.getElementById('p-name');
            const elIndustry = document.getElementById('p-industry');
            const elDesc     = document.getElementById('p-desc');
            const elContact  = document.getElementById('p-contact');
            const elEmail    = document.getElementById('p-email');

            if (elName)     elName.value     = c.company_name   || '';
            if (elIndustry) elIndustry.value = c.industry_field || '';
            if (elDesc)     elDesc.value     = c.description    || '';
            if (elContact)  elContact.value  = c.contact        || '';
            if (elEmail)    elEmail.value    = c.email          || '';

            const initials    = (c.company_name || window.userData?.name || '--').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            const placeholder = document.getElementById('logo-placeholder');
            if (placeholder) placeholder.textContent = initials;

            const img          = document.getElementById('logo-img');
            const topbarAvatar = document.getElementById('topbarAvatar');

            if (c.company_logo) {
                if (img) { img.src = c.company_logo; img.style.display = 'block'; }
                if (placeholder) placeholder.style.display = 'none';
                if (topbarAvatar) {
                    topbarAvatar.textContent              = '';
                    topbarAvatar.style.backgroundImage    = `url('${c.company_logo}')`;
                    topbarAvatar.style.backgroundSize     = 'cover';
                    topbarAvatar.style.backgroundPosition = 'center';
                    topbarAvatar.style.color              = 'transparent';
                }
            } else {
                if (img) img.style.display = 'none';
                if (placeholder) placeholder.style.display = 'block';
                if (topbarAvatar) {
                    topbarAvatar.style.backgroundImage = 'none';
                    topbarAvatar.textContent           = initials;
                    topbarAvatar.style.color           = '';
                }
            }
        }
    } catch (e) {
        console.error('loadProfile error:', e);
    }
}

function applyCrop() {
    const img = document.getElementById('crop-img');
    if (!img) return;

    const canvas = document.createElement('canvas');
    canvas.width  = CROP_SIZE;
    canvas.height = CROP_SIZE;
    const ctx     = canvas.getContext('2d');

    const cx    = CROP_SIZE / 2 - cropState.ox * cropState.scale;
    const cy    = CROP_SIZE / 2 - cropState.oy * cropState.scale;
    const drawW = img.naturalWidth  * cropState.scale;
    const drawH = img.naturalHeight * cropState.scale;
    ctx.drawImage(img, cx - drawW / 2, cy - drawH / 2, drawW, drawH);

    const croppedDataUrl  = canvas.toDataURL('image/png');
    const previewImg      = document.getElementById('logo-img');
    const placeholder     = document.getElementById('logo-placeholder');
    if (previewImg)  { previewImg.src = croppedDataUrl; previewImg.style.display = 'block'; }
    if (placeholder) { placeholder.style.display = 'none'; }

    canvas.toBlob(async (blob) => {
        const formData = new FormData();
        formData.append('logo', blob, 'logo.png');

        try {
            const response = await fetch('/api/profile/logo', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: formData
            });
            const data = await response.json();
            showToast(data.success ? '✓ ' + data.message : '✗ ' + data.message);

            if (data.success && data.logo_url) {
                const topbarAvatar = document.getElementById('topbarAvatar');
                if (topbarAvatar) {
                    topbarAvatar.textContent              = '';
                    topbarAvatar.style.backgroundImage    = `url('${data.logo_url}')`;
                    topbarAvatar.style.backgroundSize     = 'cover';
                    topbarAvatar.style.backgroundPosition = 'center';
                    topbarAvatar.style.color              = 'transparent';
                }
            }

            document.getElementById('crop-container').style.display = 'none';
        } catch (e) {
            showToast('✗ Gagal upload logo.');
        }
    }, 'image/png');
}

async function saveProfile(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append('company_name',   document.getElementById('p-name').value);
    formData.append('industry_field', document.getElementById('p-industry').value);
    formData.append('description',    document.getElementById('p-desc').value);
    formData.append('contact',        document.getElementById('p-contact').value);

    const btn      = document.querySelector('#form-profile [type="submit"]');
    const origText = btn?.innerHTML;
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...'; }

    try {
        const response = await fetch('/api/profile', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: formData
        });

        const data = await response.json();
        showToast(data.success ? '✓ Profil berhasil diperbarui' : '✗ ' + (data.message || 'Gagal memperbarui profil'));

        if (data.success) {
            if (window.userData) window.userData.name = formData.get('company_name');
            const greetName = document.getElementById('greetName');
            if (greetName) greetName.textContent = formData.get('company_name');
            await loadProfile();
        }
    } catch (e) {
        console.error(e);
        showToast('✗ Terjadi kesalahan saat menyimpan data.');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = origText; }
    }
}

async function handleSaveSettings() {
    const username = document.getElementById('set-username').value.trim();
    const phone    = document.getElementById('set-phone').value.trim();

    if (!username) {
        showToast('✗ Nama pengguna tidak boleh kosong.');
        return;
    }

    const btn      = document.querySelector('[onclick="handleSaveSettings()"]');
    const origText = btn?.innerHTML;
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...'; }

    try {
        const res = await fetch('/api/settings/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ username, phone })
        });
        const d = await res.json();
        showToast(d.success ? '✓ Pengaturan berhasil disimpan!' : '✗ ' + d.message);

        if (d.success && window.userData) {
            window.userData.name = username;
            const greetName = document.getElementById('greetName');
            if (greetName) greetName.textContent = username;
        }
    } catch (e) {
        showToast('✗ Gagal update pengaturan.');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = origText; }
    }
}

async function handleSavePassword(event) {
    event.preventDefault();

    const oldPass  = document.getElementById('set-current-pass').value;
    const newPass  = document.getElementById('set-new-pass').value;
    const confPass = document.getElementById('set-confirm-pass').value;

    if (!oldPass || !newPass || !confPass) {
        showToast('✗ Semua field password wajib diisi.');
        return;
    }
    if (newPass !== confPass) {
        showToast('✗ Konfirmasi password tidak cocok.');
        return;
    }
    if (newPass.length < 8) {
        showToast('✗ Password baru minimal 8 karakter.');
        return;
    }

    const btn      = document.querySelector('#form-password [type="submit"]');
    const origText = btn?.innerHTML;
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...'; }

    try {
        const res = await fetch('/api/profile/password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ old_password: oldPass, new_password: newPass })
        });
        const d = await res.json();
        showToast(d.success ? '✓ Password berhasil diubah!' : '✗ ' + d.message);

        if (d.success) {
            document.getElementById('set-current-pass').value = '';
            document.getElementById('set-new-pass').value     = '';
            document.getElementById('set-confirm-pass').value = '';
        }
    } catch (e) {
        showToast('✗ Gagal ganti password.');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = origText; }
    }
}

function showEditLowongan(l) {
    document.getElementById('edit-id-internship').value = l.id_internship;
    document.getElementById('edit-title').value         = l.title          || '';
    document.getElementById('edit-work-mode').value     = l.work_mode      || '';
    document.getElementById('edit-payment').value       = l.payment_status || '';
    document.getElementById('edit-location').value      = l.location       || '';
    document.getElementById('edit-quota').value         = l.quota          || '';
    document.getElementById('edit-duration').value      = l.duration       || '';
    document.getElementById('edit-deadline').value      = l.deadline ? l.deadline.substring(0, 10) : '';
    document.getElementById('edit-description').value   = l.description    || '';
    document.getElementById('edit-requirement').value   = l.requirement    || '';
    document.getElementById('edit-benefit').value       = l.benefit        || '';

    const docInfo = document.getElementById('edit-doc-existing');
    if (docInfo) docInfo.textContent = l.supporting_document
        ? '✓ Dokumen sudah ada (kosongkan jika tidak ingin mengganti)'
        : 'Belum ada dokumen';

    const modal = new bootstrap.Modal(document.getElementById('editLowonganModal'));
    modal.show();
}

async function handleEditLowongan() {
    const formData = new FormData();
    formData.append('id_internship',  document.getElementById('edit-id-internship').value);
    formData.append('title',          document.getElementById('edit-title').value.trim());
    formData.append('description',    document.getElementById('edit-description').value.trim());
    formData.append('requirements',   document.getElementById('edit-requirement').value.trim());
    formData.append('benefit',        document.getElementById('edit-benefit').value.trim());
    formData.append('location',       document.getElementById('edit-location').value);
    formData.append('work_mode',      document.getElementById('edit-work-mode').value);
    formData.append('payment_status', document.getElementById('edit-payment').value);
    formData.append('quota',          document.getElementById('edit-quota').value);
    formData.append('duration',       document.getElementById('edit-duration').value.trim());
    formData.append('deadline',       document.getElementById('edit-deadline').value);

    const imageFile = document.getElementById('edit-image')?.files[0];
    if (imageFile) formData.append('image', imageFile);

    const docFile = document.getElementById('edit-doc')?.files[0];
    if (docFile) formData.append('supporting_document', docFile);

    const btn = document.getElementById('edit-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    try {
        const response = await fetch('/api/internship/update', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editLowonganModal')).hide();
            showToast('✓ ' + data.message);
            initializeDashboard();
        } else {
            showToast('✗ ' + data.message);
        }
    } catch (e) {
        showToast('✗ Terjadi kesalahan, coba lagi.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan Perubahan';
    }
}

async function handleDeleteLowongan(idInternship, title) {
    if (!confirm(`Hapus lowongan "${title}"? Tindakan ini tidak bisa dibatalkan.`)) return;

    try {
        const response = await fetch('/api/internship/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id_internship: idInternship })
        });
        const data = await response.json();
        showToast(data.success ? '✓ ' + data.message : '✗ ' + data.message);
        if (data.success) initializeDashboard();
    } catch (e) {
        showToast('✗ Terjadi kesalahan, coba lagi.');
    }
}

function toggleSwitch(el) {
    el.classList.toggle('active');
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    const btn  = field.nextElementSibling;
    if (btn) {
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.toggle('bi-eye',       !isPassword);
            icon.classList.toggle('bi-eye-slash',  isPassword);
        }
    }
}

async function loadActivities() {
    try {
        const response  = await fetch('/api/activities');
        const data      = await response.json();
        const container = document.getElementById('activity-log-container');
        if (!container || !data.success) return;

        if (data.data.length === 0) {
            container.innerHTML = '<p style="font-size:12px;color:var(--muted)">Belum ada aktivitas</p>';
            return;
        }

        container.innerHTML = data.data.map((a, i) => `
            <div style="padding:8px 0;${i < data.data.length - 1 ? 'border-bottom:1px solid var(--border)' : ''}">
                <div style="font-weight:500;color:var(--text);font-size:12px">${a.action}</div>
                <div style="font-size:11px;margin-top:2px;color:var(--muted)">
                    ${new Date(a.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                </div>
            </div>
        `).join('');
    } catch (e) {
        console.error('loadActivities error:', e);
    }
}

async function updatePelamarStatus(status) {
    if (!['pending', 'reviewed', 'accepted', 'rejected'].includes(status)) {
        showToast('✗ Status tidak valid');
        return;
    }
    const idStudent    = document.getElementById('det-id-student').value;
    const idInternship = document.getElementById('det-id-internship').value;

    try {
        const response = await fetch('/api/internship/applicant-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id_student: idStudent, id_internship: idInternship, status })
        });
        const data = await response.json();
        showToast(data.success ? '✓ ' + data.message : '✗ ' + data.message);
        if (data.success) {
            // Update objek pelamar di _allPelamar lalu refresh detail yang sedang dibuka
            const idx = _allPelamar.findIndex(p =>
                p.id_student === idStudent && p.id_internship === idInternship
            );
            if (idx !== -1) {
                _allPelamar[idx].application_status = status;
                showDetailPelamar(_allPelamar[idx]);
            }
            // Refresh stats & progress di background
            initializeDashboard();
        }
    } catch (e) {
        showToast('✗ Terjadi kesalahan, coba lagi.');
    }
}

async function loadSettings() {
    try {
        const response = await fetch('/api/profile');
        const data     = await response.json();
        if (data.success) {
            const c          = data.data;
            const emailEl    = document.getElementById('set-email');
            const phoneEl    = document.getElementById('set-phone');
            const usernameEl = document.getElementById('set-username');
            if (emailEl)    emailEl.value    = c.email        || '';
            if (phoneEl)    phoneEl.value    = c.contact      || '';
            if (usernameEl) usernameEl.value = c.company_name || '';
        }
    } catch (e) {
        console.error('loadSettings error:', e);
    }
}

async function logout() {
    if (confirm('Anda yakin ingin logout?')) {
        await fetch('/logout', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        });
        window.location.href = '/';
    }
}