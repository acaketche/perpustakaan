@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-bar text-primary"></i>
                Analitik Perpustakaan
            </h1>
            <p class="text-muted">Laporan dan statistik penggunaan perpustakaan digital</p>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-outline-success" onclick="exportData()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar"></i> Statistik Bulanan (12 Bulan Terakhir)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th>Pembaca Unik</th>
                                    <th>Total Sesi</th>
                                    <th>Waktu Baca (Jam)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyStats as $stat)
                                    <tr>
                                        <td>{{ DateTime::createFromFormat('!m', $stat->month)->format('F') }} {{ $stat->year }}</td>
                                        <td>{{ $stat->unique_readers }}</td>
                                        <td>{{ $stat->total_sessions }}</td>
                                        <td>{{ number_format($stat->total_time / 60, 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Books and Category Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star"></i> Buku Paling Populer
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($popularBooks as $book)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <h6 class="mb-1">{{ Str::limit($book->title, 30) }}</h6>
                                <small class="text-muted">{{ $book->author }}</small>
                                <br>
                                <span class="badge bg-primary">{{ $book->category->name }}</span>
                            </div>
                            <span class="badge bg-info fs-6">
                                {{ $book->reading_logs_count }} pembaca
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Statistik Kategori
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($categoryStats as $category)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ $category->name }}</span>
                                <span>{{ $category->published_books_count }} buku</span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar"
                                     style="width: {{ $categoryStats->max('published_books_count') > 0 ? ($category->published_books_count / $categoryStats->max('published_books_count')) * 100 : 0 }}%">
                                    {{ $category->total_readers }} pembaca
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- User Activity and Recent Uploads -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> Aktivitas Pengguna
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($userActivity as $activity)
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-capitalize">{{ $activity->role }}</span>
                            <span class="badge bg-secondary">{{ $activity->count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Upload Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Diupload oleh</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentUploads as $upload)
                                    <tr>
                                        <td>{{ Str::limit($upload->title, 30) }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $upload->category->name }}</span>
                                        </td>
                                        <td>{{ $upload->uploader->name }}</td>
                                        <td>{{ $upload->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportData() {
    // Simple CSV export functionality
    const data = [
        ['Bulan', 'Pembaca Unik', 'Total Sesi', 'Waktu Baca (Jam)'],
        @foreach($monthlyStats as $stat)
            ['{{ DateTime::createFromFormat("!m", $stat->month)->format("F") }} {{ $stat->year }}', '{{ $stat->unique_readers }}', '{{ $stat->total_sessions }}', '{{ number_format($stat->total_time / 60, 1) }}'],
        @endforeach
    ];

    const csvContent = data.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'analytics-' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush
@endsection
