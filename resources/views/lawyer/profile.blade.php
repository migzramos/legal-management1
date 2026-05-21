@extends('layouts.lawyer')

@section('title', 'Profile & Settings')

@section('content')
<style>
    .profile-topbar { margin-bottom: 28px; }
    .profile-topbar h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: 4px; }
    .profile-topbar p { font-size: .875rem; color: var(--text-muted); }

    .profile-card { border-radius: 12px; border: 1px solid var(--border-color, rgba(255,255,255,.08)); background: var(--card-bg); margin-bottom: 20px; overflow: hidden; }
    .profile-card-header { padding: 16px 24px; border-bottom: 1px solid var(--border-color, rgba(255,255,255,.08)); }
    .profile-card-title { font-size: .95rem; font-weight: 600; }
    .profile-card-body { padding: 24px; }

    .profile-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .profile-form-grid:last-child { margin-bottom: 0; }
    @media (max-width: 600px) { .profile-form-grid { grid-template-columns: 1fr; } }

    .profile-field { display: flex; flex-direction: column; gap: 6px; }
    .profile-field label { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); }
    .profile-field input { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color, rgba(255,255,255,.12)); background: var(--input-bg, rgba(0,0,0,.2)); color: inherit; font-size: .9rem; outline: none; transition: border-color .2s; }
    .profile-field input:focus { border-color: var(--purple-core, #7c5cbf); }

    .profile-prefix-wrap { position: relative; display: flex; align-items: center; }
    .profile-prefix-wrap .prefix { position: absolute; left: 12px; font-size: .9rem; color: var(--text-muted); pointer-events: none; user-select: none; }
    .profile-prefix-wrap input { padding-left: 26px; }

    .profile-avatar-row { display: flex; align-items: center; gap: 14px; padding: 6px 0; }
    .profile-avatar-circle { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, var(--purple-core, #7c5cbf), var(--purple-light, #a07ce0)); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; color: #fff; flex-shrink: 0; }
    .profile-avatar-name { font-size: .9rem; font-weight: 500; margin-bottom: 2px; }
    .profile-avatar-sub { font-size: .8rem; color: var(--text-muted); }

    .profile-hint { font-size: .78rem; color: var(--text-muted); margin-top: 6px; }

    .profile-alert { padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: .875rem; }
    .profile-alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
    .profile-alert-error { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }
    .profile-alert ul { padding-left: 16px; margin: 0; }

    .profile-save-row { display: flex; justify-content: flex-end; margin-top: 4px; }
    .profile-save-btn { background: var(--purple-core, #7c5cbf); color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-size: .9rem; font-weight: 600; cursor: pointer; transition: opacity .2s; }
    .profile-save-btn:hover { opacity: .85; }
</style>

<div class="profile-topbar">
    <h1>Profile & Settings</h1>
    <p>Manage your personal information and professional settings</p>
</div>

@if(session('success'))
<div class="profile-alert profile-alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="profile-alert profile-alert-error">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('lawyer.profile.update') }}" method="POST" style="max-width: 860px;">
    @csrf
    @method('PUT')

    {{-- Personal Information --}}
    <div class="profile-card">
        <div class="profile-card-header">
            <span class="profile-card-title">Personal Information</span>
        </div>
        <div class="profile-card-body">
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="profile-field">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $user->email) }}" required>
                </div>
            </div>
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone"
                           value="{{ old('phone', $user->phone) }}" placeholder="e.g. 09XX-XXX-XXXX">
                </div>
                <div class="profile-field">
                    <label>Profile Avatar</label>
                    <div class="profile-avatar-row">
                        <div class="profile-avatar-circle">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="profile-avatar-name">{{ $user->name }}</div>
                            <div class="profile-avatar-sub">Initial generated from your name</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Professional Settings --}}
    <div class="profile-card">
        <div class="profile-card-header">
            <span class="profile-card-title">Professional Settings</span>
        </div>
        <div class="profile-card-body">
            <div class="profile-field" style="max-width: 320px;">
                <label for="hourly_rate">Hourly Rate *</label>
                <div class="profile-prefix-wrap">
                    <span class="prefix">₱</span>
                    <input type="number" id="hourly_rate" name="hourly_rate"
                           step="0.01" min="0"
                           value="{{ old('hourly_rate', $user->hourly_rate ?? 0) }}" required>
                </div>
                <span class="profile-hint">Current rate: {{ money_display($user->hourly_rate ?? 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="profile-card">
        <div class="profile-card-header">
            <span class="profile-card-title">Change Password</span>
        </div>
        <div class="profile-card-body">
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                           placeholder="Enter current password">
                </div>
                <div class="profile-field">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password"
                           minlength="8" placeholder="Min. 8 characters">
                </div>
            </div>
            <div class="profile-form-grid">
                <div class="profile-field">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation"
                           name="new_password_confirmation" placeholder="Repeat new password">
                </div>
                <div></div>{{-- spacer --}}
            </div>
            <p class="profile-hint">Leave password fields empty if you don't want to change your password.</p>
        </div>
    </div>

    <div class="profile-save-row">
        <button type="submit" class="profile-save-btn">Save Changes</button>
    </div>
</form>
@endsection