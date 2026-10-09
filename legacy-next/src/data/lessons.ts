import type { Lesson, SignSystem } from '@/types';
const makeLesson = (id: string, title: string, subtitle: string, unit: string, letter: string, description: string): Lesson => ({ id, title, subtitle, unit, xp: 10, exercises: [
  { id: `${id}-intro`, kind: 'info', prompt: `Kenalan dengan ${title}`, answer: letter, detail: description },
  { id: `${id}-quiz`, kind: 'choice', prompt: `Tanda apakah ini? Petunjuk: ${description}`, answer: letter, choices: [letter, ...['A', 'B', 'C', 'D'].filter(choice => choice !== letter).slice(0, 3)] },
  { id: `${id}-select`, kind: 'select', prompt: `Pilih gesture untuk ${letter}`, answer: `Gesture ${letter}`, choices: ['Gesture 1', `Gesture ${letter}`, 'Gesture 3'] },
  { id: `${id}-match`, kind: 'matching', prompt: 'Pasangkan tanda dengan huruf yang tepat', answer: letter, choices: ['A', 'B', 'C', 'D'] },
  { id: `${id}-practice`, kind: 'practice', prompt: `Praktikkan gesture ${letter}`, answer: letter, detail: 'Arahkan tangan ke kamera. Hasil deteksi ini disimulasikan untuk demo.' },
] });
const sibi = [
  makeLesson('alphabet-a','Alfabet Dasar','Huruf A','Unit 1 · Alfabet','A','Kepalkan tangan dengan ibu jari berada di sisi telunjuk.'),
  makeLesson('alphabet-b','Alfabet Lanjutan','Huruf B','Unit 1 · Alfabet','B','Rapatkan empat jari dan tekuk ibu jari ke telapak.'),
  makeLesson('numbers','Mengenal Angka','Angka 1–5','Unit 2 · Angka','C','Belajar mengenali bentuk tangan untuk angka sederhana.'),
  makeLesson('words','Kata Sehari-hari','Salam dan sapaan','Unit 3 · Kata Dasar','D','Sapaan sederhana membantu memulai percakapan.'),
  makeLesson('sentences','Kalimat Sederhana','Susun kalimat','Unit 4 · Komunikasi Dasar','E','Gabungkan tanda menjadi pesan yang mudah dipahami.'),
  makeLesson('school','Di Sekolah','Percakapan sekolah','Unit 5 · Percakapan Sekolah','A','Berlatih percakapan yang sering digunakan di sekolah.'),
  makeLesson('daily','Percakapan Sehari-hari','Cerita harian','Unit 6 · Percakapan','B','Gunakan bahasa isyarat dalam situasi sehari-hari.'),
];
const bisindo = sibi.map((lesson, i) => ({ ...lesson, id: `bisindo-${lesson.id}`, title: ['Alfabet BISINDO','Isyarat Huruf B','Angka BISINDO','Salam BISINDO','Kalimat BISINDO','Di Sekolah','Percakapan Harian'][i], subtitle: lesson.subtitle.replace('Huruf','Isyarat huruf'), exercises: lesson.exercises.map(e => ({...e, id: `bisindo-${e.id}`, detail: e.detail ? `${e.detail} Materi demonstrasi BISINDO.` : undefined})) }));
export const lessonsBySystem: Record<SignSystem, Lesson[]> = { SIBI: sibi, BISINDO: bisindo };
export const sections = [
  { title: 'Dasar Bahasa Isyarat', description: 'Mulai dari bentuk tangan yang paling dasar', units: ['Unit 1 · Alfabet','Unit 2 · Angka','Unit 3 · Kata Dasar'] },
  { title: 'Komunikasi Dasar', description: 'Rangkai tanda menjadi percakapan', units: ['Unit 4 · Komunikasi Dasar','Unit 5 · Percakapan Sekolah'] },
  { title: 'Komunikasi Sehari-hari', description: 'Bawa keterampilanmu ke keseharian', units: ['Unit 6 · Percakapan'] },
];
