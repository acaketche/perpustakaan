<?php

namespace Database\Seeders;

use App\Models\DigitalBook;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DigitalBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $dosen = User::where('role', 'dosen')->first();

        $categories = Category::all();

        $books = [
            [
                'title' => 'Algoritma dan Struktur Data',
                'author' => 'Dr. Ahmad Susanto',
                'publication_year' => 2023,
                'description' => 'Buku komprehensif tentang algoritma dan struktur data untuk mahasiswa informatika.',
                'pdf_path' => 'digital-books/sample_1.pdf',
                'total_pages' => 350,
                'file_size' => 4500000,
                'category_id' => $categories->where('slug', 'informatika')->first()->id,
                'uploaded_by' => $admin->id,
                'status' => 'published',
            ],
            [
                'title' => 'Arsitektur Komputer Modern',
                'author' => 'Prof. Budi Hartono',
                'publication_year' => 2022,
                'description' => 'Panduan lengkap arsitektur komputer dari dasar hingga teknologi terkini.',
                'pdf_path' => 'digital-books/sample_2.pdf',
                'total_pages' => 420,
                'file_size' => 5200000,
                'category_id' => $categories->where('slug', 'teknik-komputer')->first()->id,
                'uploaded_by' => $dosen->id,
                'status' => 'published',
            ],
            [
                'title' => 'Manajemen Konstruksi',
                'author' => 'Ir. Siti Nurhaliza',
                'publication_year' => 2023,
                'description' => 'Konsep dan praktik manajemen konstruksi untuk proyek teknik sipil.',
                'pdf_path' => 'digital-books/sample_3.pdf',
                'total_pages' => 280,
                'file_size' => 3800000,
                'category_id' => $categories->where('slug', 'teknik-sipil')->first()->id,
                'uploaded_by' => $dosen->id,
                'status' => 'published',
            ],
            [
                'title' => 'Matematika Diskrit',
                'author' => 'Dr. Eko Prasetyo',
                'publication_year' => 2022,
                'description' => 'Dasar-dasar matematika diskrit untuk semua program studi teknik.',
                'pdf_path' => 'digital-books/sample_4.pdf',
                'total_pages' => 310,
                'file_size' => 4100000,
                'category_id' => $categories->where('slug', 'umum')->first()->id,
                'uploaded_by' => $admin->id,
                'status' => 'published',
            ]
        ];

        foreach ($books as $book) {
            DigitalBook::create($book);
        }
    }
}
