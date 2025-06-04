@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-book text-primary"></i>
                Kelola Buku Digital
            </h1>
            <p class="text-muted">Manajemen koleksi buku digital perpustakaan</p>
        </div>
        <a href="{{ route('digital-books.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Tambah Buku Baru
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Buku</label>
                    <input type="text" name="search" class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Judul atau pengarang...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
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
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>
                            Published
                        </option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Urutkan</label>
                    <select name="sort" class="form-select">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>
                            Terbaru
                        </option>
                        <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>
                            Judul
                        </option>
                        <option value="author" {{ request('sort') == 'author' ? 'selected' : '' }}>
                            Pengarang
                        </option>
                        <option value="readers" {{ request('sort') == 'readers' ? 'selected' : '' }}>
                            Pembaca
                        </option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Books Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th>Pembaca</th>
                            <th>Diupload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($books as $book)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $book->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $book->total_pages }} halaman</small>
                                    </div>
                                </td>
                                <td>{{ $book->author }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $book->category->name }}</span>
                                </td>
                                <td>{{ $book->publication_year }}</td>
                                <td>
                                    @if($book->status == 'published')
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $book->reading_logs_count }}</span>
                                </td>
                                <td>
                                    <small>
                                        {{ $book->created_at->format('d/m/Y') }}<br>
                                        oleh {{ $book->uploader->name }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('digital-books.show', $book) }}"
                                           class="btn btn-outline-primary" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('digital-books.edit', $book) }}"
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('digital-books.destroy', $book) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"
                                                    title="Hapus"
                                                    onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Tidak ada buku ditemukan</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
