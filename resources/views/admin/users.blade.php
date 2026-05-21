<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management — LegalCase</title>
    @include('admin.partials.styles')
    <style>
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }
        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: border-color .22s, transform .22s;
        }
        .kpi-card:hover { border-color: rgba(124,58,237,0.35); transform: translateY(-2px); }
        .kpi-card::before { content: ''; position: absolute; inset: 0; border-radius: 16px; opacity: 0; transition: opacity .22s; }
        .kpi-card:hover::before { opacity: 1; }
        .kpi-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .kpi-label { font-size: 0.68rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--text-muted); }
        .kpi-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .kpi-icon svg { width: 17px; height: 17px; }
        .kpi-value { font-family: 'Cormorant Garamond', serif; font-size: 2.6rem; font-weight: 700; line-height: 1; color: var(--text-primary); }
        .kpi-meta { font-size: 0.73rem; color: var(--text-muted); margin-top: 6px; }
        .user-count-badge { background: rgba(124,58,237,0.15); color: var(--purple-light); border: 1px solid rgba(124,58,237,0.25); border-radius: 20px; font-size: 0.72rem; font-weight: 600; padding: 2px 10px; }
        .toolbar-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .search-wrap { position: relative; }
        .search-wrap svg { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; width: 14px; height: 14px; }
        .search-input { background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 10px; padding: 8px 14px 8px 34px; color: var(--text-primary); font-family: 'DM Sans', sans-serif; font-size: 0.83rem; outline: none; width: 230px; transition: border-color .2s, box-shadow .2s; }
        .search-input::placeholder { color: var(--text-muted); }
        .search-input:focus { border-color: var(--purple-core); box-shadow: 0 0 0 3px rgba(124,58,237,0.14); }
        .filter-tabs { display: flex; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 10px; padding: 3px; gap: 2px; }
        .ftab { background: transparent; border: none; border-radius: 8px; padding: 5px 14px; color: var(--text-muted); font-family: 'DM Sans', sans-serif; font-size: 0.78rem; font-weight: 500; cursor: pointer; transition: all .15s; white-space: nowrap; }
        .ftab:hover { color: var(--text-primary); }
        .ftab.active { background: rgba(124,58,237,0.2); color: var(--purple-light); }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600; flex-shrink: 0; }
        .user-cell { display: flex; align-items: center; gap: 10px; }
        .user-name { font-size: 0.87rem; font-weight: 500; color: var(--text-primary); }
        .user-phone { font-size: 0.72rem; color: var(--text-muted); margin-top: 1px; }
        .email-mono { font-size: 0.78rem; color: var(--text-muted); font-family: monospace; }
        .act-group { display: flex; gap: 4px; justify-content: flex-end; }
        .act-btn { background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; padding: 6px 8px; cursor: pointer; color: var(--text-muted); transition: all .15s; display: inline-flex; align-items: center; line-height: 1; }
        .act-btn svg { width: 13px; height: 13px; }
        .act-btn:hover { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,.2); color: var(--text-primary); }
        .act-btn.danger:hover { border-color: rgba(248,113,113,.4); color: var(--danger); background: rgba(248,113,113,0.08); }
        .act-btn.warn:hover { border-color: rgba(251,191,36,.4); color: var(--warning); background: rgba(251,191,36,0.08); }
        .act-btn.toggle-off { color: var(--danger); border-color: rgba(248,113,113,.2); }
        .act-btn.toggle-off:hover { background: rgba(248,113,113,0.08); border-color: rgba(248,113,113,.4); }
        .empty-row td { padding: 56px 0; text-align: center; color: var(--text-muted); font-size: 0.84rem; }
        .empty-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(124,58,237,0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
        .empty-icon svg { width: 22px; height: 22px; color: var(--purple-light); opacity: .5; }
    </style>
</head>
<body>
<div class="app-layout">
    @include('admin.partials.sidebar')

    <div class="main-area">

        <!-- Topbar -->
        <div class="topbar">
            <div>
                <div class="topbar-title">User Management</div>
                <div class="topbar-subtitle">Manage lawyers, clients and administrators</div>
            </div>
            <div class="topbar-right">
                <a href="#" class="btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export PDF
                </a>
                <button class="btn-primary" onclick="openModal('addUserModal')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="17" y1="11" x2="23" y2="11"/>
                    </svg>
                    Add User
                </button>
            </div>
        </div>

        <div class="page-content">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            <div id="flash-msg"></div>

            <!-- KPI Cards -->
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Total Users</span>
                        <div class="kpi-icon" style="background:rgba(124,58,237,0.12);color:var(--purple-light);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $totalUsers }}</div>
                    <div class="kpi-meta">Registered accounts</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Lawyers</span>
                        <div class="kpi-icon" style="background:rgba(251,191,36,0.12);color:var(--warning);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $lawyerCount }}</div>
                    <div class="kpi-meta">Legal professionals</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Clients</span>
                        <div class="kpi-icon" style="background:rgba(96,165,250,0.12);color:var(--info);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $clientCount }}</div>
                    <div class="kpi-meta">Active clients</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-card-top">
                        <span class="kpi-label">Admins</span>
                        <div class="kpi-icon" style="background:rgba(248,113,113,0.12);color:var(--danger);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/></svg>
                        </div>
                    </div>
                    <div class="kpi-value">{{ $adminCount }}</div>
                    <div class="kpi-meta">System administrators</div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">
                            All Users
                            <span class="user-count-badge" id="userCountBadge">{{ $totalUsers }}</span>
                        </div>
                        <div class="card-subtitle">Browse and manage all registered accounts</div>
                    </div>
                    <div class="toolbar-right">
                        <div class="search-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input class="search-input" id="srch" placeholder="Search users…" oninput="doSearch()">
                        </div>
                        <div class="filter-tabs">
                            <button class="ftab active" onclick="doFilter('all',this)">All</button>
                            <button class="ftab" onclick="doFilter('lawyer',this)">Lawyers</button>
                            <button class="ftab" onclick="doFilter('client',this)">Clients</button>
                            <button class="ftab" onclick="doFilter('admin',this)">Admins</button>
                        </div>
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="table" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Assigned Lawyer</th>
                                <th>Cases</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbody"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ✅ Add User Modal — now submits to real backend -->
<div class="modal-overlay" id="addUserModal" onclick="if(event.target===this)closeModal('addUserModal')">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('addUserModal')">✕</button>
        <div class="modal-title">Add New User</div>
        <div class="modal-sub">Create a new account with a specific role</div>

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="John Doe"
                           value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="john@example.com"
                           value="{{ old('email') }}" required>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="client"  {{ old('role') === 'client'  ? 'selected' : '' }}>Client</option>
                        <option value="lawyer"  {{ old('role') === 'lawyer'  ? 'selected' : '' }}>Lawyer</option>
                        <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="+1 234 567 8900"
                           value="{{ old('phone') }}">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="button" class="btn-ghost" onclick="closeModal('addUserModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal (still JS only — wire up separately if needed) -->
<div class="modal-overlay" id="editUserModal" onclick="if(event.target===this)closeModal('editUserModal')">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('editUserModal')">✕</button>
        <div class="modal-title">Edit User</div>
        <div class="modal-sub">Update account details</div>
        <form onsubmit="handleEditUser(event)">
            <input type="hidden" id="editUserId">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" id="editName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="editEmail" class="form-control" required>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select id="editRole" class="form-control">
                        <option value="client">Client</option>
                        <option value="lawyer">Lawyer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" id="editPhone" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">New Password
                    <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted);">(leave blank to keep current)</span>
                </label>
                <input type="password" class="form-control" placeholder="Optional">
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="button" class="btn-ghost" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Lawyer Modal -->
<div class="modal-overlay" id="assignModal" onclick="if(event.target===this)closeModal('assignModal')">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('assignModal')">✕</button>
        <div class="modal-title" id="assignTitle">Assign Lawyer</div>
        <div class="modal-sub" id="assignSub">Assign a lawyer to this client</div>
        <form onsubmit="handleAssign(event)">
            <input type="hidden" id="assignUserId">
            <div class="form-group">
                <label class="form-label">Select Lawyer</label>
                <select id="assignLawyerSelect" class="form-control" required>
                    <option value="">— Choose a lawyer —</option>
                </select>
            </div>
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="button" class="btn-ghost" onclick="closeModal('assignModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">Assign Lawyer</button>
            </div>
        </form>
    </div>
</div>

<script>
const palette = ['#7c3aed','#34d399','#fbbf24','#f87171','#60a5fa','#a855f7','#fb923c','#38bdf8'];
function avatarColor(id){ return palette[(id-1) % palette.length]; }
function initials(n){ return n.trim().split(/\s+/).map(w=>w[0]).slice(0,2).join('').toUpperCase(); }
function escJs(s){ return s.replace(/'/g,"\\'"); }

let users = {!! json_encode($users->map(fn($u) => [
    'id'       => $u->id,
    'name'     => $u->name,
    'email'    => $u->email,
    'role'     => $u->role,
    'phone'    => $u->phone ?? '',
    'cases'    => $u->cases_count + $u->client_cases_count,
    'joined'   => $u->created_at->format('M Y'),
    'active'   => (bool)$u->is_active,
    'assigned' => null,
])) !!};

let nextId = {{ $users->max('id') + 1 }};
let activeRole = 'all';
const lawyers = {!! json_encode($lawyers->map(fn($l) => ['id' => $l->id, 'name' => $l->name])) !!};

function render(list) {
    const tb = document.getElementById('tbody');
    document.getElementById('userCountBadge').textContent = list.length;

    if (!list.length) {
        tb.innerHTML = `<tr class="empty-row"><td colspan="8">
            <div class="empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                    <path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            No users found matching your search
        </td></tr>`;
        return;
    }

    tb.innerHTML = list.map(u => {
        const c = avatarColor(u.id);
        const roleBadge = {
            lawyer: ['badge-warning', 'LAWYER'],
            client: ['badge-purple',  'CLIENT'],
            admin:  ['badge-danger',  'ADMIN'],
        }[u.role] || ['badge-secondary', u.role.toUpperCase()];

        const assignedCell = u.role === 'client'
            ? (u.assigned
                ? `<div style="display:flex;align-items:center;gap:6px;">
                    <div style="width:6px;height:6px;border-radius:50%;background:var(--success);flex-shrink:0;"></div>
                    <span style="font-size:.82rem;color:var(--text-primary);">${u.assigned}</span>
                   </div>`
                : `<span style="color:var(--text-muted);font-size:.78rem;">Not assigned</span>`)
            : `<span style="color:var(--text-muted);">—</span>`;

        const assignBtn = u.role === 'client'
            ? `<button class="act-btn warn" title="Assign lawyer" onclick="openAssignModal(${u.id},'${escJs(u.name)}')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <polyline points="17 11 19 13 23 9"/>
                </svg>
               </button>`
            : '';

        return `<tr data-role="${u.role}" data-id="${u.id}">
            <td>
                <div class="user-cell">
                    <div class="user-avatar" style="background:${c}22;color:${c};border:1px solid ${c}33;">
                        ${initials(u.name)}
                    </div>
                    <div>
                        <div class="user-name">${u.name}</div>
                        ${u.phone ? `<div class="user-phone">${u.phone}</div>` : ''}
                    </div>
                </div>
            </td>
            <td><span class="email-mono">${u.email}</span></td>
            <td><span class="badge ${roleBadge[0]}">${roleBadge[1]}</span></td>
            <td>${assignedCell}</td>
            <td>
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="font-size:.87rem;font-weight:500;">${u.cases}</span>
                    <span style="font-size:.72rem;color:var(--text-muted);">case${u.cases !== 1 ? 's' : ''}</span>
                </div>
            </td>
            <td><span style="font-size:.78rem;color:var(--text-muted);">${u.joined}</span></td>
            <td><span class="${u.active ? 'status-active' : 'status-closed'}">${u.active ? 'Active' : 'Inactive'}</span></td>
            <td>
                <div class="act-group">
                    <button class="act-btn" title="Edit user" onclick="openEditModal(${u.id})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="act-btn ${u.active ? 'toggle-off' : ''}" title="${u.active ? 'Deactivate' : 'Activate'}" onclick="toggleActive(${u.id})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 11-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                    </button>
                    ${assignBtn}
                    <button class="act-btn danger" title="Delete user" onclick="deleteUser(${u.id})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function getFiltered() {
    const q = document.getElementById('srch').value.toLowerCase();
    return users.filter(u =>
        (activeRole === 'all' || u.role === activeRole) &&
        (u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q))
    );
}
function doSearch() { render(getFiltered()); }
function doFilter(role, btn) {
    activeRole = role;
    document.querySelectorAll('.ftab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    doSearch();
}
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

// Edit user (JS only — no backend wired yet)
function openEditModal(id) {
    const u = users.find(x => x.id === id);
    if (!u) return;
    document.getElementById('editUserId').value = id;
    document.getElementById('editName').value   = u.name;
    document.getElementById('editEmail').value  = u.email;
    document.getElementById('editRole').value   = u.role;
    document.getElementById('editPhone').value  = u.phone || '';
    openModal('editUserModal');
}
function handleEditUser(e) {
    e.preventDefault();
    const id = parseInt(document.getElementById('editUserId').value);
    const u  = users.find(x => x.id === id);
    if (!u) return;
    u.name  = document.getElementById('editName').value.trim();
    u.email = document.getElementById('editEmail').value.trim();
    u.role  = document.getElementById('editRole').value;
    u.phone = document.getElementById('editPhone').value.trim();
    closeModal('editUserModal');
    flash('User updated successfully.');
    render(getFiltered());
}
function toggleActive(id) {
    const u = users.find(x => x.id === id);
    if (!u) return;
    if (!confirm(`${u.active ? 'Deactivate' : 'Activate'} ${u.name}?`)) return;
    u.active = !u.active;
    render(getFiltered());
    flash(`User ${u.active ? 'activated' : 'deactivated'}.`);
}
function deleteUser(id) {
    const u = users.find(x => x.id === id);
    if (!confirm(`Delete ${u?.name}? This cannot be undone.`)) return;
    users = users.filter(x => x.id !== id);
    flash('User deleted.');
    render(getFiltered());
}
function openAssignModal(userId, userName) {
    document.getElementById('assignUserId').value = userId;
    document.getElementById('assignTitle').textContent = 'Assign Lawyer';
    document.getElementById('assignSub').textContent   = `Assign a lawyer to ${userName}`;
    const sel = document.getElementById('assignLawyerSelect');
    sel.innerHTML = '<option value="">— Choose a lawyer —</option>' +
        lawyers.map(l => `<option value="${escJs(l.name)}">${l.name}</option>`).join('');
    openModal('assignModal');
}
function handleAssign(e) {
    e.preventDefault();
    const userId     = parseInt(document.getElementById('assignUserId').value);
    const lawyerName = document.getElementById('assignLawyerSelect').value;
    const u = users.find(x => x.id === userId);
    if (u) u.assigned = lawyerName || null;
    closeModal('assignModal');
    flash('Lawyer assigned successfully.');
    render(getFiltered());
}
function flash(msg) {
    const el = document.getElementById('flash-msg');
    el.innerHTML = `<div class="alert alert-success" style="margin-bottom:16px;">${msg}</div>`;
    setTimeout(() => el.innerHTML = '', 3000);
}

// Auto-open modal if there were validation errors
@if($errors->any())
    document.addEventListener('DOMContentLoaded', () => openModal('addUserModal'));
@endif

render(getFiltered());
</script>
</body>
</html>