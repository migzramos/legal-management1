<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Details — LegalCase</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @include('client.partials.styles')
    <style>
        .detail-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
        .detail-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 24px; }
        .detail-card h2 { font-family: 'Cormorant Garamond', serif; margin-bottom: 12px; }
        .detail-meta { display: grid; gap: 14px; margin-top: 18px; }
        .detail-meta-item label { display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 4px; }
        .detail-meta-item span { display: block; font-size: 0.95rem; color: var(--text-primary); }
    </style>
</head>
<body>
<div class="bg-scene"></div>
<div class="app">
    @include('client.partials.sidebar')
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <h1>Document Details</h1>
                <p>Preview metadata for {{ $document->title }}</p>
            </div>
            <div class="topbar-right">
                <a href="{{ route('client.documents.index', $document->case->id) }}" class="btn-secondary">Back to Documents</a>
            </div>
        </div>
        <div class="content">
            <div class="detail-grid">
                <div class="detail-card">
                    <h2>{{ $document->title }}</h2>
                    <p style="color: var(--text-secondary); line-height: 1.8;">{{ $document->notes ?? 'No additional notes were provided for this document.' }}</p>
                    <div class="detail-meta">
                        <div class="detail-meta-item"><label>Case</label><span>{{ $document->case->case_number }} — {{ $document->case->title }}</span></div>
                        <div class="detail-meta-item"><label>Uploaded By</label><span>{{ $document->uploader->name ?? 'System' }}</span></div>
                        <div class="detail-meta-item"><label>File Name</label><span>{{ $document->file_name }}</span></div>
                        <div class="detail-meta-item"><label>File Type</label><span>{{ $document->file_type }}</span></div>
                        <div class="detail-meta-item"><label>Size</label><span>{{ number_format($document->file_size / 1024, 2) }} KB</span></div>
                        <div class="detail-meta-item"><label>Visibility</label><span>{{ $document->is_visible_to_client ? 'Visible to client' : 'Private' }}</span></div>
                        <div class="detail-meta-item"><label>Version</label><span>{{ $document->version }}</span></div>
                    </div>
                </div>
                <div class="detail-card" style="display:flex; flex-direction:column; justify-content:space-between; gap:16px;">
                    <div>
                        <h3 style="margin-bottom:14px;">File Access</h3>
                        <p style="color: var(--text-secondary); line-height: 1.7;">This document is stored securely in the system. If your file is not available for download, contact your lawyer for access.</p>
                    </div>
                    <div>
                        @if($document->is_visible_to_client)
                            <a class="btn-primary" href="{{ route('client.documents.download', $document) }}">Download Document</a>
                        @else
                            <button class="btn-secondary" disabled>Download unavailable</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
