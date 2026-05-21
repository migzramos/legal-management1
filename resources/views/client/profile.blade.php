@extends('layouts.client')
@section('title', 'My Profile')
@section('content')

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="section-header">
    <div>
        <h1 class="section-title">My Profile</h1>
        <p class="section-subtitle">Manage your personal information and account settings.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-body" style="text-align:center;padding:28px 20px;">
                <div class="avatar avatar-xl" style="margin:0 auto 16px;font-size:2rem;width:80px;height:80px;border-radius:16px;background:linear-gradient(135deg,var(--purple-core),var(--purple-light));display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:600;color:#fff;">
                    {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                </div>
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:600;color:var(--text-primary);margin-bottom:4px;">{{ Auth::user()->name }}</div>
                <div style="font-size:0.78rem;color:var(--text-muted);">{{ Auth::user()->email }}</div>
                <div style="margin-top:14px;"><span class="badge badge-purple">Client</span></div>
            </div>
            <div style="border-top:1px solid var(--border);padding:16px 20px;display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.75rem;color:var(--text-muted);">Member since</span>
                    <span style="font-size:0.82rem;color:var(--text-secondary);">{{ Auth::user()->created_at->format('M Y') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.75rem;color:var(--text-muted);">Last updated</span>
                    <span style="font-size:0.82rem;color:var(--text-secondary);">{{ Auth::user()->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
            @csrf
            <button type="submit" class="btn-danger" style="width:100%;justify-content:center;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:15px;height:15px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign Out
            </button>
        </form>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px;">
        <div class="card">
            <div class="card-header"><div class="card-title">Personal Information</div></div>
            <div class="card-body">
                <form method="POST" action="{{ route('client.profile.update') }}">
                    @csrf @method('PATCH')
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name',Auth::user()->name) }}" required>
                            @error('name')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email',Auth::user()->email) }}" required>
                            @error('email')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group" style="grid-column:span 2;">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone',Auth::user()->phone ?? '') }}" placeholder="+63 9XX XXX XXXX">
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;padding-top:6px;">
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Change Password</div></div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" autocomplete="current-password">
                        @error('current_password')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" autocomplete="new-password">
                            @error('password')<div class="form-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" autocomplete="new-password">
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;padding-top:6px;">
                        <button type="submit" class="btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
