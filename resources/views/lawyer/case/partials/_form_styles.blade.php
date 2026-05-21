<style>
    .cf-alert { padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .875rem; }
    .cf-alert-error { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }
    .cf-alert ul { margin: .5rem 0 0 1.25rem; }

    .cf-card { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: 12px; margin-bottom: 1.5rem; overflow: hidden; }
    .cf-card-hd { display: flex; align-items: center; gap: .875rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,.06); }
    .cf-card-hd-icon { width: 36px; height: 36px; min-width: 36px; display: flex; align-items: center; justify-content: center; background: rgba(139,92,246,.15); border-radius: 8px; color: #a78bfa; }
    .cf-card-hd-icon svg { width: 18px; height: 18px; }
    .cf-section-title { font-size: .9375rem; font-weight: 600; color: #f1f5f9; }
    .cf-section-sub { font-size: .8125rem; color: #94a3b8; margin-top: 2px; }
    .cf-card-body { padding: 1.5rem; }

    .cf-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
    .cf-field { display: flex; flex-direction: column; gap: .375rem; }
    .cf-field-full { grid-column: 1 / -1; }

    .cf-label { font-size: .8125rem; font-weight: 500; color: #cbd5e1; }
    .cf-req { color: #f87171; }
    .cf-input { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-radius: 8px; padding: .625rem .875rem; color: #f1f5f9; font-size: .875rem; width: 100%; transition: border-color .2s; outline: none; }
    .cf-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
    .cf-input::placeholder { color: #475569; }
    .cf-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .75rem center; background-size: 16px; padding-right: 2.5rem; }
    .cf-select option { background: #1e1b2e; color: #f1f5f9; }
    .cf-textarea { resize: vertical; min-height: 100px; }
    .cf-error { font-size: .75rem; color: #f87171; margin-top: 2px; }

    .cf-actions { display: flex; gap: 1rem; align-items: center; padding-top: .5rem; margin-bottom: 2rem; }

    @media (max-width: 640px) {
        .cf-grid { grid-template-columns: 1fr; }
    }
</style>