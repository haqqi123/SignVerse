<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Material;
use Illuminate\Database\Seeder;

/*
 * Paritas dengan _seed_materials_and_lessons() di signlib/db.py:
 * 6 materi (alfabet/angka/kosakata × SIBI/BISINDO) + lessons lengkap
 * (26 huruf per sistem, angka 1–10, kosakata dasar).
 */
class MaterialLessonSeeder extends Seeder
{
    private const NUM_WORDS = [
        '1' => 'SATU', '2' => 'DUA', '3' => 'TIGA', '4' => 'EMPAT', '5' => 'LIMA',
        '6' => 'ENAM', '7' => 'TUJUH', '8' => 'DELAPAN', '9' => 'SEMBILAN',
    ];

    public function run(): void
    {
        $materials = [
            ['alfabet', 'SIBI', 'Alfabet SIBI', 'Alfabet isyarat A–Z Sistem Isyarat Bahasa Indonesia (SIBI) resmi.', 0],
            ['alfabet', 'BISINDO', 'Alfabet BISINDO', 'Alfabet isyarat A–Z dengan ragam Bahasa Isyarat Indonesia (BISINDO).', 1],
            ['angka', 'SIBI', 'Angka SIBI', 'Angka 1–10 SIBI. Dipraktikkan lewat ejaan abjad (contoh: SATU = S-A-T-U).', 2],
            ['angka', 'BISINDO', 'Angka BISINDO', 'Angka 1–10 BISINDO dengan variasi regional.', 3],
            ['kosakata', 'SIBI', 'Kosakata Dasar SIBI', 'Kata sehari-hari SIBI untuk komunikasi dasar.', 4],
            ['kosakata', 'BISINDO', 'Kosakata Dasar BISINDO', 'Kata sehari-hari ragam BISINDO.', 5],
        ];

        $mat = [];
        foreach ($materials as [$category, $system, $title, $description, $order]) {
            $m = Material::updateOrCreate(
                ['title' => $title],
                ['category' => $category, 'sign_system' => $system, 'description' => $description, 'sort_order' => $order],
            );
            $mat[$title] = $m;
        }

        $letters = range('A', 'Z');
        foreach (['SIBI', 'BISINDO'] as $system) {
            $title = $system === 'SIBI' ? 'Alfabet SIBI' : 'Alfabet BISINDO';
            foreach ($letters as $i => $ch) {
                Lesson::updateOrCreate(
                    ['material_id' => $mat[$title]->id, 'title' => "Huruf {$ch}"],
                    [
                        'target' => $ch, 'practice_mode' => 'letter', 'practice_target' => $ch,
                        'description' => "Isyarat huruf {$ch} ({$system}). Posisi telapak tangan menghadap ke depan, jari-jari dibuka jelas.",
                        'sort_order' => $i,
                    ],
                );
            }
        }

        // Angka SIBI: 1–9 letter + 10 word (SEPULUH)
        foreach (str_split('123456789') as $i => $n) {
            Lesson::updateOrCreate(
                ['material_id' => $mat['Angka SIBI']->id, 'title' => "Angka {$n}"],
                [
                    'target' => $n, 'practice_mode' => 'letter', 'practice_target' => self::NUM_WORDS[$n],
                    'description' => "Isyarat angka {$n} SIBI. Latihan: tunjukkan ejaan ".self::NUM_WORDS[$n].' bagian demi bagian.',
                    'sort_order' => $i,
                ],
            );
        }
        Lesson::updateOrCreate(
            ['material_id' => $mat['Angka SIBI']->id, 'title' => 'Angka 10'],
            [
                'target' => '10', 'practice_mode' => 'word', 'practice_target' => 'SEPULUH',
                'description' => 'Angka sepuluh SIBI. Latihan: ejaan S-E-P-U-L-U-H.', 'sort_order' => 9,
            ],
        );

        // Angka BISINDO: 1–9 letter
        foreach (str_split('123456789') as $i => $n) {
            Lesson::updateOrCreate(
                ['material_id' => $mat['Angka BISINDO']->id, 'title' => "Angka {$n}"],
                [
                    'target' => $n, 'practice_mode' => 'letter', 'practice_target' => self::NUM_WORDS[$n],
                    'description' => "Angka {$n} BISINDO. Latihan melalui ejaan ".self::NUM_WORDS[$n].'.',
                    'sort_order' => $i,
                ],
            );
        }

        $kosakataSibi = [
            ['SAYA', 'Mengarahkan telapak tangan ke dada. Ejaan S-A-Y-A.'],
            ['MAKAN', 'Isyarat makan: tangan menuju mulut. Ejaan M-A-K-A-N.'],
            ['MINUM', 'Seperti memegang gelas. Ejaan M-I-N-U-M.'],
            ['PAGI', 'Sapaan waktu pagi. Ejaan P-A-G-I.'],
            ['BELAJAR', 'Isyarat belajar/buku. Ejaan B-E-L-A-J-A-R.'],
            ['TERIMA KASIH', 'Ejaan gabungan dua kata (spasi).'],
        ];
        foreach ($kosakataSibi as $i => [$word, $desc]) {
            Lesson::updateOrCreate(
                ['material_id' => $mat['Kosakata Dasar SIBI']->id, 'title' => $word],
                ['target' => $word, 'practice_mode' => 'word', 'practice_target' => $word, 'description' => $desc, 'sort_order' => $i],
            );
        }

        $kosakataBisindo = [
            ['SAYA', 'Menunjuk diri. Ejaan S-A-Y-A.'],
            ['MAKAN', 'BISINDO: jari menutup lalu membuka dekat mulut.'],
            ['MINUM', 'BISINDO: tangan memegang gelas imajiner.'],
            ['PAGI', 'Ejaan P-A-G-I.'],
        ];
        foreach ($kosakataBisindo as $i => [$word, $desc]) {
            Lesson::updateOrCreate(
                ['material_id' => $mat['Kosakata Dasar BISINDO']->id, 'title' => $word],
                ['target' => $word, 'practice_mode' => 'word', 'practice_target' => $word, 'description' => $desc, 'sort_order' => $i],
            );
        }
    }
}
