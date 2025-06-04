@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="fas fa-plus-circle text-success"></i>
                        Tambah Buku Digital Baru
                    </h2>
                    <p class="text-muted">Unggah buku digital baru ke perpustakaan</p>
                </div>
                <a href="{{ route('digital-books.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- Form Card -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-upload"></i> Form Upload Buku Digital
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('digital-books.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-info-circle"></i> Informasi Dasar
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label">
                                    Judul Buku <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="publication_year" class="form-label">
                                    Tahun Terbit <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('publication_year') is-invalid @enderror"
                                       id="publication_year" name="publication_year"
                                       value="{{ old('publication_year', date('Y')) }}"
                                       min="1900" max="{{ date('Y') }}" required>
                                @error('publication_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="author" class="form-label">
                                    Pengarang <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('author') is-invalid @enderror"
                                       id="author" name="author" value="{{ old('author') }}" required>
                                @error('author')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="category_id" class="form-label">
                                    Program Studi <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id" required>
                                    <option value="">Pilih Program Studi</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4"
                                      placeholder="Deskripsi singkat tentang buku ini...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- File Upload -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-file-pdf"></i> File PDF
                                </h6>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="pdf_file" class="form-label">
                                File PDF <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="file" class="form-control @error('pdf_file') is-invalid @enderror"
                                       id="pdf_file" name="pdf_file" accept="application/pdf" required>
                                <span class="input-group-text">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </span>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Format file harus PDF. Ukuran maksimal 20MB.
                            </div>
                            @error('pdf_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- File Preview -->
                            <div id="filePreview" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                    <span id="fileName"></span>
                                    <span id="fileSize" class="text-muted"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Publication Status -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="fas fa-eye"></i> Status Publikasi
                                </h6>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status"
                                               id="status_published" value="published"
                                               {{ old('status', 'published') == 'published' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_published">
                                            <i class="fas fa-globe text-success"></i>
                                            <strong>Publikasikan Sekarang</strong>
                                            <br>
                                            <small class="text-muted">Buku akan langsung tersedia untuk dibaca</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status"
                                               id="status_draft" value="draft"
                                               {{ old('status') == 'draft' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_draft">
                                            <i class="fas fa-edit text-warning"></i>
                                            <strong>Simpan sebagai Draft</strong>
                                            <br>
                                            <small class="text-muted">Buku disimpan tapi belum dipublikasikan</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('digital-books.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Simpan Buku Digital
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pdfFileInput = document.getElementById('pdf_file');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    pdfFileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            const sizeInMB = (file.size / 1024 / 1024).toFixed(2);

            fileName.textContent = file.name;
            fileSize.textContent = `(${sizeInMB} MB)`;
            filePreview.style.display = 'block';

            // Validate file size
            if (file.size > 20 * 1024 * 1024) { // 20MB
                alert('Ukuran file terlalu besar! Maksimal 20MB.');
                this.value = '';
                filePreview.style.display = 'none';
            }
        } else {
            filePreview.style.display = 'none';
        }
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const author = document.getElementById('author').value.trim();
        const category = document.getElementById('category_id').value;
        const pdfFile = document.getElementById('pdf_file').files[0];

        if (!title || !author || !category || !pdfFile) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi!');
            return false;
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        submitBtn.disabled = true;
    });
});
</script>
@endpush
@endsection
