<?php

namespace App\Http\Controllers;

use App\Models\DigitalBook;
use App\Models\Category;
use App\Models\User;
use App\Models\ReadingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        // Basic statistics
        $stats = [
            'total_books' => DigitalBook::count(),
            'published_books' => DigitalBook::where('status', 'published')->count(),
            'total_readers' => User::where('role', 'mahasiswa')->count(),
            'total_reading_time' => ReadingLog::sum('total_reading_time'),
        ];

        // Reading trends for the last 30 days
        $readingTrends = ReadingLog::select(
                DB::raw('DATE(last_accessed_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as readers'),
                DB::raw('SUM(total_reading_time) as total_time')
            )
            ->where('last_accessed_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Popular books by category
        $popularBooksByCategory = Category::with(['digitalBooks' => function ($query) {
                $query->withCount('readingLogs')
                      ->where('status', 'published')
                      ->orderBy('reading_logs_count', 'desc')
                      ->limit(5);
            }])
            ->withCount(['digitalBooks' => function ($query) {
                $query->where('status', 'published');
            }])
            ->get();

        return view('admin.dashboard', compact('stats', 'readingTrends', 'popularBooksByCategory'));
    }

    /**
     * Show books management page.
     */
    public function books(Request $request)
    {
        $query = DigitalBook::with(['category', 'uploader'])
                           ->withCount('readingLogs');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        switch ($sortBy) {
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'author':
                $query->orderBy('author', $sortOrder);
                break;
            case 'year':
                $query->orderBy('publication_year', $sortOrder);
                break;
            case 'readers':
                $query->orderBy('reading_logs_count', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $books = $query->paginate(15)->withQueryString();
        $categories = Category::all();

        return view('admin.books', compact('books', 'categories'));
    }

    /**
     * Show analytics page.
     */
    public function analytics()
    {
        // Monthly reading statistics
        $monthlyStats = ReadingLog::select(
                DB::raw('YEAR(last_accessed_at) as year'),
                DB::raw('MONTH(last_accessed_at) as month'),
                DB::raw('COUNT(DISTINCT user_id) as unique_readers'),
                DB::raw('COUNT(*) as total_sessions'),
                DB::raw('SUM(total_reading_time) as total_time')
            )
            ->where('last_accessed_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Most popular books
        $popularBooks = DigitalBook::withCount('readingLogs')
                                  ->with('category')
                                  ->where('status', 'published')
                                  ->orderBy('reading_logs_count', 'desc')
                                  ->limit(10)
                                  ->get();

        // Category statistics
        $categoryStats = Category::withCount(['digitalBooks as published_books_count' => function ($query) {
                $query->where('status', 'published');
            }])
            ->with(['digitalBooks' => function ($query) {
                $query->withCount('readingLogs')
                      ->where('status', 'published');
            }])
            ->get()
            ->map(function ($category) {
                $totalReaders = $category->digitalBooks->sum('reading_logs_count');
                $category->total_readers = $totalReaders;
                return $category;
            });

        // User activity
        $userActivity = User::select('role', DB::raw('COUNT(*) as count'))
                           ->groupBy('role')
                           ->get();

        // Recent uploads
        $recentUploads = DigitalBook::with(['uploader', 'category'])
                                   ->orderBy('created_at', 'desc')
                                   ->limit(10)
                                   ->get();

        return view('admin.analytics', compact(
            'monthlyStats',
            'popularBooks',
            'categoryStats',
            'userActivity',
            'recentUploads'
        ));
    }
}
