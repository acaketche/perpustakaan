@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="bg-primary-gradient text-white rounded-4 p-5 text-center">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-book-open"></i> Perpustakaan Digital
                </h1>
                <p class="lead mb-4">Akses ribuan buku digital dari berbagai program studi</p>
                @can('create', App\Models\DigitalBook::class)
                    <a href="{{ route('digital-books.create') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-plus"></i> Tambah Buku Digital
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> Filter Buku
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cari Buku</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control"
                                       value="{{ request('search') }}"
                                       placeholder="Judul atau Pengarang">
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Program Studi</label>
                            <select name="category" class="form-select">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}"
                                            {{ request('category') == $category->slug ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tahun Terbit</label>
                            <input type="number" name="year" class="form-control"
                                   value="{{ request('year') }}"
                                   min="1900" max="{{ date('Y') }}"
                                   placeholder="Contoh: 2023">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('digital-books.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Statistik
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Buku:</span>
                        <span class="fw-bold">{{ $books->total() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Kategori:</span>
                        <span class="fw-bold">{{ $categories->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Grid -->
        <div class="col-lg-9">
            <!-- Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    @if(request('search') || request('category') || request('year'))
                        Hasil Pencarian
                    @else
                        Katalog Buku Digital
                    @endif
                    <small class="text-muted">({{ $books->total() }} buku)</small>
                </h4>

                <!-- Sort Options -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-sort"></i> Urutkan
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?{{ http_build_query(array_merge(request()->all(), ['sort' => 'title'])) }}">Judul A-Z</a></li>
                        <li><a class="dropdown-item" href="?{{ http_build_query(array_merge(request()->all(), ['sort' => 'year'])) }}">Tahun Terbaru</a></li>
                        <li><a class="dropdown-item" href="?{{ http_build_query(array_merge(request()->all(), ['sort' => 'popular'])) }}">Paling Populer</a></li>
                    </ul>
                </div>
            </div>

            <!-- Books Grid -->
            <div class="row">
                @forelse($books as $book)
                    <div class="col-md-6 col-xl-4 mb-4">
                        <div class="card h-100 shadow-sm book-card">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary">{{ $book->category->name }}</span>
                                    <small class="text-muted">{{ $book->publication_year }}</small>
                                </div>

                                <h5 class="card-title">{{ $book->title }}</h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-user"></i> {{ $book->author }}
                                </p>

                                <p class="card-text flex-grow-1">
                                    {{ Str::limit($book->description, 100) }}
                                </p>

                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-file-pdf text-danger"></i><br>
                                            {{ $book->total_pages }} hal
                                        </small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">
                                            <i class="fas fa-users text-info"></i><br>
                                            {{ $book->total_readers }} pembaca
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('digital-books.read', $book) }}" class="btn btn-success">
                                        <i class="fas fa-book-open"></i> Baca Sekarang
                                    </a>
                                    <a href="{{ route('digital-books.show', $book) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Tidak ada buku ditemukan</h4>
                            <p class="text-muted">Silakan coba dengan kata kunci atau filter yang berbeda.</p>
                            <a href="{{ route('digital-books.index') }}" class="btn btn-primary">
                                <i class="fas fa-undo"></i> Lihat Semua Buku
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($books->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $books->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
