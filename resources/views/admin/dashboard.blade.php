@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt text-primary"></i>
                Dashboard Administrator
            </h1>
            <p class="text-muted">Selamat datang di panel admin perpustakaan digital</p>
        </div>
        <div class="text-muted">
            <i class="fas fa-calendar"></i>
            {{ now()->format('d F Y') }}
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Buku
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_books'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Buku Terpublikasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['published_books'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Pembaca
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_readers'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Waktu Baca
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ floor($stats['total_reading_time'] / 60) }} jam
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Reading Trends Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i>
                        Tren Pembacaan (30 Hari Terakhir)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="readingTrendsChart"
                            data-labels="{{ json_encode($readingTrends->pluck('date')) }}"
                            data-readers="{{ json_encode($readingTrends->pluck('readers')) }}"
                            data-reading-time="{{ json_encode($readingTrends->pluck('total_time')->map(fn($time) => $time / 60)) }}">
                    </canvas>
                </div>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i>
                        Distribusi Kategori
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryDistributionChart"
                            data-categories="{{ json_encode($popularBooksByCategory->pluck('name')) }}"
                            data-book-counts="{{ json_encode($popularBooksByCategory->pluck('published_books_count')) }}">
                    </canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Books by Category -->
    <div class="row">
        @foreach($popularBooksByCategory as $category)
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-star"></i>
                        Buku Populer - {{ $category->name }}
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($category->digitalBooks as $book)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <h6 class="mb-1">{{ Str::limit($book->title, 40) }}</h6>
                                <small class="text-muted">{{ $book->author }}</small>
                            </div>
                            <span class="badge bg-info">
                                {{ $book->reading_logs_count }} pembaca
                            </span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-info-circle"></i>
                            Belum ada buku di kategori ini
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reading Trends Chart
    const readingTrendsChart = document.getElementById('readingTrendsChart');
    if (readingTrendsChart) {
        const labels = JSON.parse(readingTrendsChart.dataset.labels);
        const readers = JSON.parse(readingTrendsChart.dataset.readers);
        const readingTime = JSON.parse(readingTrendsChart.dataset.readingTime);

        new Chart(readingTrendsChart, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Jumlah Pembaca',
                        data: readers,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        yAxisID: 'y',
                    },
                    {
                        label: 'Waktu Baca (jam)',
                        data: readingTime,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Jumlah Pembaca'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Waktu Baca (jam)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }

    // Category Distribution Chart
    const categoryChart = document.getElementById('categoryDistributionChart');
    if (categoryChart) {
        const categories = JSON.parse(categoryChart.dataset.categories);
        const bookCounts = JSON.parse(categoryChart.dataset.bookCounts);

        new Chart(categoryChart, {
            type: 'pie',
            data: {
                labels: categories,
                datasets: [{
                    data: bookCounts,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
