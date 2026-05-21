<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Documents — LegalCase</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @include('client.partials.styles')
    <style>
        .grid-two { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; }
        .document-row { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 14px; padding: 18px 0; border-bottom: 1px solid var(--border); }
        .document-row:last-child { border-bottom: none; }
        .document-details h3 { font-size: 1rem; margin-bottom: 6px; }
        .document-details p { margin: 0; color: var(--text-secondary); font-size: 0.9rem; }
        .upload-panel { display: flex; flex-direction: column; gap: 16px; }
        .upload-panel h3 { margin: 0 0 8px; }
        .file-help { font-size: 0.85rem; color: var(--text-muted); }
    </style>
</head>
<body>
<div class="bg-scene"></div>
<div class="app">
    @include('client.partials.sidebar')
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <h1>Case Documents</h1>
                <p>View and upload documents for case {{ $case->case_number }}</p>
            </div>
            <div class="topbar-right">
                <a href="{{ route('client.cases.show', $case->id) }}" class="btn-secondary">Back to Case</a>
            </div>
        </div>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif
            <div class="grid-two">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Shared Documents</span>
                        <a href="{{ route('client.messages.index', $case->id) }}" class="card-action">Send a message</a>
                    </div>
                    <div class="card-body">
                        @if($documents->count())
                            @foreach($documents as $document)
                            <div class="document-row">
                                <div class="document-details">
                                    <h3>{{ $document->title }}</h3>
                                    <p>{{ $document->file_name }} • Uploaded by {{ $document->uploader->name ?? 'System' }}</p>
                                </div>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <a href="{{ route('client.documents.show', $document->id) }}" class="btn-secondary">Details</a>
                                    <span class="badge {{ $document->is_visible_to_client ? 'badge-success' : 'badge-warning' }}">{{ $document->is_visible_to_client ? 'Visible' : 'Hidden' }}</span>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <h3>No documents found for this case</h3>
                                <p>Documents your lawyer shares will appear here.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card upload-panel">
                    <div class="card-header">
                        <span class="card-title">Upload Document</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('client.documents.upload', $case->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="title">Document Title</label>
                                <input id="title" name="title" type="text" class="form-control" value="{{ old('title') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="file">Upload File</label>
                                <input id="file" name="file" type="file" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea id="notes" name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                            </div>
                            <p class="file-help">Max file size 20 MB. Only documents required by your lawyer should be submitted.</p>
                            <button type="submit" class="btn-primary">Upload Document</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
