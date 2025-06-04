@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('digital-books.index') }}">
                            <i class="fas fa-book"></i> Buku Digital
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ $digitalBook->title }}</li>
                </ol>
            </nav>

            <!-- Book Detail Card -->
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">{{ $digitalBook->title }}</h3>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-light text-primary fs-6">
                                {{ $digitalBook->category->name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Book Info -->
                            <div class="mb-4">
                                <h5 class="text-primary">
                                    <i class="fas fa-info-circle"></i> Informasi Buku
                                </h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150"><strong>Pengarang:</strong></td>
                                        <td>{{ $digitalBook->author }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tahun Terbit:</strong></td>
                                        <td>{{ $digitalBook->publication_year }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kategori:</strong></td>
                                        <td>{{ $digitalBook->category->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jumlah Halaman:</strong></td>
                                        <td>{{ $digitalBook->total_pages }} halaman</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ukuran File:</strong></td>
                                        <td>{{ $digitalBook->file_size_formatted }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Pembaca:</strong></td>
                                        <td>{{ $digitalBook->total_readers }} orang</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Description -->
                            @if($digitalBook->description)
                            <div class="mb-4">
                                <h5 class="text-primary">
                                    <i class="fas fa-align-left"></i> Deskripsi
                                </h5>
                                <p class="text-justify">{{ $digitalBook->description }}</p>
                            </div>
                            @endif

                            <!-- Reading Progress -->
                            @if($readingLog)
                            <div class="mb-4">
                                <h5 class="text-primary">
                                    <i class="fas fa-bookmark"></i> Progress Baca Anda
                                </h5>
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Halaman Terakhir:</strong> {{ $readingLog->last_page_read }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Total Waktu Baca:</strong> {{ floor($readingLog->total_reading_time / 60) }} jam {{ $readingLog->total_reading_time % 60 }} menit
                                        </div>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" style="width: {{ ($readingLog->last_page_read / $digitalBook->total_pages) * 100 }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        Progress: {{ number_format(($readingLog->last_page_read / $digitalBook->total_pages) * 100, 1) }}%
                                    </small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Aksi</h6>

                                    <div class="d-grid gap-2">
                                        <a href="{{ route('digital-books.read', $digitalBook) }}" class="btn btn-success btn-lg">
                                            <i class="fas fa-book-open"></i>
                                            @if($readingLog)
                                                Lanjut Baca
                                            @else
                                                Mulai Baca
                                            @endif
                                        </a>

                                        @can('update', $digitalBook)
                                        <a href="{{ route('digital-books.edit', $digitalBook) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        @endcan

                                        @can('delete', $digitalBook)
                                        <form action="{{ route('digital-books.destroy', $digitalBook) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100"
                                                    onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                        @endcan
                                    </div>

                                    <hr>

                                    <!-- Share Buttons -->
                                    <h6>Bagikan</h6>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick="shareBook('facebook')">
                                            <i class="fab fa-facebook"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm"
                                                onclick="shareBook('twitter')">
                                            <i class="fab fa-twitter"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm"
                                                onclick="shareBook('whatsapp')">
                                            <i class="fab fa-whatsapp"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                onclick="copyLink()">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function shareBook(platform) {
    const url = window.location.href;
    const title = '{{ $digitalBook->title }}';
    const text = `Baca buku "${title}" di Perpustakaan Digital`;

    let shareUrl = '';

    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
            break;
    }

    if(shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Link berhasil disalin!');
    });
}
</script>
@endpush
@endsection
