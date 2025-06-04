@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- PDF Controls -->
            <div class="pdf-controls bg-white shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="fas fa-book-open text-primary"></i>
                            {{ $digitalBook->title }}
                        </h5>
                        <small class="text-muted">{{ $digitalBook->author }}</small>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <!-- Zoom Controls -->
                            <div class="btn-group" role="group">
                                <button id="zoomOut" class="btn btn-outline-secondary btn-sm" title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button id="zoomIn" class="btn btn-outline-secondary btn-sm" title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                            </div>

                            <!-- Fullscreen -->
                            <button id="fullscreen" class="btn btn-outline-secondary btn-sm" title="Fullscreen">
                                <i class="fas fa-expand"></i>
                            </button>

                            <!-- Back Button -->
                            <a href="{{ route('digital-books.show', $digitalBook) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PDF Viewer Container -->
            <div id="pdfViewer" class="pdf-viewer-container text-center"
                 data-pdf-url="{{ route('digital-books.pdf', $digitalBook) }}"
                 data-book-id="{{ $digitalBook->id }}"
                 data-watermark-text="PERPUSTAKAAN DIGITAL"
                 data-user-name="{{ Auth::user()->name }}">

                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="d-flex justify-content-center align-items-center" style="height: 400px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Memuat PDF...</span>
                </div>
            </div>

            <!-- Navigation Controls -->
            <div class="pdf-controls bg-white shadow-sm mt-0">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <button id="prevPage" class="btn btn-outline-primary" disabled>
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </button>

                            <span class="mx-3">
                                Halaman
                                <input type="number" id="pageInput" class="form-control d-inline-block text-center"
                                       style="width: 80px;" min="1" max="{{ $digitalBook->total_pages }}" value="1">
                                dari <span id="totalPages">{{ $digitalBook->total_pages }}</span>
                            </span>

                            <button id="nextPage" class="btn btn-outline-primary">
                                Selanjutnya <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center gap-3">
                            <!-- Reading Progress -->
                            <div class="text-muted">
                                <small>
                                    <i class="fas fa-clock"></i>
                                    Waktu baca: <span id="readingTime">0</span> menit
                                </small>
                            </div>

                            <!-- Page Jump -->
                            <button id="goToPage" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-right"></i> Go
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Set PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// Initialize reading time tracker
let readingStartTime = Date.now();
let totalReadingTime = 0;

// Update reading time display
function updateReadingTime() {
    totalReadingTime = Math.floor((Date.now() - readingStartTime) / 60000);
    document.getElementById('readingTime').textContent = totalReadingTime;
}

// Update reading time every minute
setInterval(updateReadingTime, 60000);

// Initialize PDF viewer when page loads
document.addEventListener('DOMContentLoaded', function() {
    const pdfContainer = document.getElementById('pdfViewer');
    const pdfUrl = pdfContainer.dataset.pdfUrl;

    if (typeof window.initPdfViewer === 'function') {
        window.initPdfViewer('pdfViewer', pdfUrl, {
            watermark: true,
            watermarkText: pdfContainer.dataset.watermarkText,
            userName: pdfContainer.dataset.userName,
            prevButton: 'prevPage',
            nextButton: 'nextPage',
            pageInput: 'pageInput',
            goToPageButton: 'goToPage',
            zoomIn: 'zoomIn',
            zoomOut: 'zoomOut'
        });
    }

    // Fullscreen functionality
    document.getElementById('fullscreen').addEventListener('click', function() {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            pdfContainer.requestFullscreen();
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'f' || e.key === 'F') {
            e.preventDefault();
            document.getElementById('fullscreen').click();
        }
        if (e.key === 'Escape' && document.fullscreenElement) {
            document.exitFullscreen();
        }
    });
});
</script>
@endpush
@endsection
