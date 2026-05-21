@extends('layouts.admin')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 32px; }
        h1 { font-size: 28px; margin-bottom: 8px; }
        p.small { font-size: 11px; color: #666; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 10px; font-size: 12px; }
        th { background: #f4f4f4; text-align: left; }
    </style>
</head>
<body>
    <h1>Admin User List</h1>
    <p class="small">Generated on {{ now()->format('F j, Y \a\t H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Cases</th>
                <th>Status</th>
                <th>Registration Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>{{ $user->cases_count + $user->client_cases_count }}</td>
                <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                <td>{{ $user->created_at->format('M j, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
@endsection