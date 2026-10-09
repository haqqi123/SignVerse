<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Material;
use Illuminate\Database\Seeder;

/**
 * Konten pembelajaran — setara data demo project lama (db.py):
 * kategori Alphabet/Number/Word/Phrase → materi → lesson per gesture.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Alphabet',
                'icon' => '🔤',
                'description' => 'Isyarat huruf A-Z (SIBI & BISINDO).',
                'materials' => [
                    [
                        'title' => 'Huruf A-M (SIBI)',
                        'language' => Material::LANGUAGE_SIBI,
                        'difficulty' => 1,
                        'lessons' => ['A', 'B', 'C', 'D', 'E'],
                    ],
                    [
                        'title' => 'Huruf N-Z (SIBI)',
                        'language' => Material::LANGUAGE_SIBI,
                        'difficulty' => 1,
                        'lessons' => ['N', 'O', 'P', 'Q', 'R'],
                    ],
                ],
            ],
            [
                'name' => 'Number',
                'icon' => '🔢',
                'description' => 'Angka 1-10 dalam bahasa isyarat.',
                'materials' => [
                    [
                        'title' => 'Angka 1-10 (BISINDO)',
                        'language' => Material::LANGUAGE_BISINDO,
                        'difficulty' => 1,
                        'lessons' => ['1', '2', '3', '4', '5'],
                    ],
                ],
            ],
            [
                'name' => 'Word',
                'icon' => '💬',
                'description' => 'Kosakata sehari-hari.',
                'materials' => [
                    [
                        'title' => 'Sapaan Dasar (BISINDO)',
                        'language' => Material::LANGUAGE_BISINDO,
                        'difficulty' => 2,
                        'lessons' => ['Terima kasih', 'Maaf', 'Permisi'],
                    ],
                ],
            ],
            [
                'name' => 'Phrase',
                'icon' => '🗣️',
                'description' => 'Frasa percakapan sederhana.',
                'materials' => [
                    [
                        'title' => 'Percakapan Sederhana (BISINDO)',
                        'language' => Material::LANGUAGE_BISINDO,
                        'difficulty' => 3,
                        'lessons' => ['Apa kabar', 'Sampai jumpa'],
                    ],
                ],
            ],
        ];

        foreach ($categories as $index => $data) {
            $category = Category::create([
                'name' => $data['name'],
                'slug' => str($data['name'])->slug(),
                'icon' => $data['icon'],
                'description' => $data['description'],
                'order' => $index,
            ]);

            foreach ($data['materials'] as $mIndex => $materialData) {
                $material = $category->materials()->create([
                    'title' => $materialData['title'],
                    'slug' => str($materialData['title'])->slug(),
                    'language' => $materialData['language'],
                    'difficulty' => $materialData['difficulty'],
                    'order' => $mIndex,
                ]);

                foreach ($materialData['lessons'] as $lIndex => $lessonTitle) {
                    $material->lessons()->create([
                        'title' => $lessonTitle,
                        'gesture_label' => str($lessonTitle)->lower()->snake()->value(),
                        'xp_reward' => 10,
                        'difficulty' => $materialData['difficulty'],
                        'order' => $lIndex,
                    ]);
                }
            }
        }
    }
}
