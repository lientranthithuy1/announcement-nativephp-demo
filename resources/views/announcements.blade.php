<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement Demo</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .announcement-item {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .seen {
            opacity: 0.6;
            border-left-color: #28a745;
        }
        .status {
            margin: 20px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔔 Announcement Demo App</h1>
        
        <div id="status" class="status info">
            <strong>Status:</strong> Running background job every minute to check for new announcements
        </div>

        <div class="form-section">
            <h3>Add New Announcement</h3>
            <form id="announcementForm">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="4" required></textarea>
                </div>
                <button type="submit">Add Announcement</button>
                <button type="button" id="clearSeen" class="btn-danger">Clear Seen List</button>
            </form>
        </div>

        <div class="announcements-section">
            <h3>List announcements ({{ count($announcements) ?? [] }} items)</h3>
            <div id="announcementsList">
                @forelse($announcements ?? [] as $announcement)
                    <div class="announcement-item {{ in_array($announcement['id'], $seen) ? 'seen' : '' }}">
                        <h4>{{ $announcement['title'] }} 
                            @if(in_array($announcement['id'], $seen))
                                <span style="color: #28a745;">✓ Seen</span>
                            @else
                                <span style="color: #dc3545;">● Unseen</span>
                            @endif
                        </h4>
                        <p>{{ $announcement['message'] }}</p>
                        <small>ID: {{ $announcement['id'] }}</small>
                    </div>
                @empty
                    <p>No announcements found</p>
                @endforelse
            </div>
        </div>

        <div class="debug-section">
            <h3>Debug Info</h3>
            <p><strong>Seen IDs:</strong> {{ implode(', ', $seen) ?: 'Không có' }}</p>
            <p><strong>Total announcements:</strong> {{ count($announcements) }}</p>
            <p><strong>Unseen:</strong> {{ count($announcements) - count($seen) }}</p>
        </div>
    </div>

    <script>
        // Set CSRF token for AJAX requests
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        document.getElementById('announcementForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/add-announcement', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('status').innerHTML = `<strong>✅ Success:</strong> ${result.message}`;
                    document.getElementById('status').className = 'status success';
                    this.reset();
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('status').innerHTML = `<strong>❌ Error:</strong> ${error.message}`;
            }
        });

        document.getElementById('clearSeen').addEventListener('click', async function() {
            if (!confirm('Are you sure you want to clear the seen list?')) return;

            try {
                const response = await fetch('/clear-seen', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('status').innerHTML = `<strong>✅ Success:</strong> ${result.message}`;
                    document.getElementById('status').className = 'status success';
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        // Auto refresh every 30 seconds to show updates
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>