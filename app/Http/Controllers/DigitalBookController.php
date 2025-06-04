<?php

namespace App\Http\Controllers;

use App\Models\DigitalBook;
use App\Models\Category;
use App\Models\ReadingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DigitalBookController extends Controller
{
    public function index(Request $request)
    {
        $query = DigitalBook::with(['category', 'uploader'])
            ->where('status', 'published');

        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('year')) {
            $query->where('publication_year', $request->year);
        }

        $books = $query->paginate(12);
        $categories = Category::all();

        return view('digital-books.index', compact('books', 'categories'));
    }

    public function show(DigitalBook $digitalBook)
    {
        if ($digitalBook->status !== 'published') {
            abort(404);
        }

        $readingLog = null;
        if (Auth::check()) {
            $readingLog = ReadingLog::where('user_id', Auth::id())
                ->where('digital_book_id', $digitalBook->id)
                ->first();
        }

        return view('digital-books.show', compact('digitalBook', 'readingLog'));
    }

    public function create()
    {
        $this->authorize('create', DigitalBook::class);
        $categories = Category::all();
        return view('digital-books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DigitalBook::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publication_year' => 'required|integer|min:1900|max:' . date('Y'),
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'pdf_file' => 'required|file|mimes:pdf|max:20480',
            'status' => 'required|in:draft,published'
        ]);

        $pdfFile = $request->file('pdf_file');
        $filename = time() . '_' . $pdfFile->getClientOriginalName();
        $path = $pdfFile->storeAs('digital-books', $filename, 'private');

        $fileSize = $pdfFile->getSize();
        $totalPages = $this->getPdfPageCount($pdfFile->getPathname());

        DigitalBook::create([
            'title' => $request->title,
            'author' => $request->author,
            'publication_year' => $request->publication_year,
            'description' => $request->description,
            'pdf_path' => $path,
            'total_pages' => $totalPages,
            'file_size' => $fileSize,
            'category_id' => $request->category_id,
            'uploaded_by' => Auth::id(),
            'status' => $request->status
        ]);

        return redirect()->route('digital-books.index')
            ->with('success', 'Buku digital berhasil ditambahkan!');
    }

    public function read(DigitalBook $digitalBook)
    {
        if ($digitalBook->status !== 'published') {
            abort(404);
        }

        if (Auth::check()) {
            ReadingLog::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'digital_book_id' => $digitalBook->id
                ],
                [
                    'last_accessed_at' => now()
                ]
            );
        }

        return view('digital-books.reader', compact('digitalBook'));
    }

    public function updateReadingProgress(Request $request, DigitalBook $digitalBook)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'page' => 'required|integer|min:1',
            'reading_time' => 'required|integer|min:0'
        ]);

        ReadingLog::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'digital_book_id' => $digitalBook->id
            ],
            [
                'last_page_read' => $request->page,
                'total_reading_time' => $request->reading_time,
                'last_accessed_at' => now()
            ]
        );

        return response()->json(['success' => true]);
    }

    private function getPdfPageCount($filePath)
    {
        $content = file_get_contents($filePath);
        $pages = preg_match_all("/\/Page\W/", $content);
        return $pages ?: 1;
    }
}
