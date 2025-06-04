<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Teknik Komputer',
                'slug' => 'teknik-komputer',
                'description' => 'Buku-buku tentang arsitektur komputer, sistem embedded, jaringan komputer, dan topik terkait teknik komputer lainnya.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Informatika',
                'slug' => 'informatika',
                'description' => 'Buku-buku tentang pemrograman, basis data, kecerdasan buatan, pengembangan perangkat lunak, dan topik informatika lainnya.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teknik Sipil',
                'slug' => 'teknik-sipil',
                'description' => 'Buku-buku tentang struktur bangunan, manajemen konstruksi, teknik lingkungan, dan topik teknik sipil lainnya.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Umum',
                'slug' => 'umum',
                'description' => 'Buku-buku yang relevan untuk semua jurusan, seperti matematika dasar, fisika, bahasa Inggris, kewirausahaan, dan pengetahuan umum lainnya.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
