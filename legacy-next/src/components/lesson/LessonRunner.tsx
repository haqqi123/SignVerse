'use client';
import { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { X, ArrowRight, Check, RotateCcw, Sparkles, Camera, Hand, Star, LockKeyhole } from 'lucide-react';
import { lessonsBySystem } from '@/data/lessons';
import { useDemo } from '@/components/providers/DemoProvider';
import { mockGamificationService } from '@/services/mock-gamification.service';

export function LessonRunner({ id }: { id: string }) {
  const router = useRouter();
  const { profile, finishLesson } = useDemo();
  const lessons = lessonsBySystem[profile.system];
  const lesson = lessons.find(item => item.id === id);
  const [step, setStep] = useState(0);
  const [choice, setChoice] = useState<string | null>(null);
  const [checked, setChecked] = useState(false);
  const [scanning, setScanning] = useState(false);
  const [result, setResult] = useState<'success' | 'retry' | null>(null);
  const [complete, setComplete] = useState(false);
  const [wrongAnswers, setWrongAnswers] = useState(0);
  const [xpEarned, setXpEarned] = useState(0);
  const [wasCompletedAtStart] = useState(() => profile.completedLessons.includes(id));

  if (!lesson) return <div className="lesson-not-found"><h1>Lesson tidak ditemukan</h1><Link href="/learn">Kembali ke jalur belajar</Link></div>;
  const lessonIndex = lessons.findIndex(item => item.id === id);
  const alreadyCompleted = wasCompletedAtStart;
  const isLocked = !alreadyCompleted && lessons.slice(0, lessonIndex).some(item => !profile.completedLessons.includes(item.id));
  if (isLocked) return <div className="focus-layout"><div className="locked-lesson"><div><LockKeyhole size={32}/></div><span className="eyebrow">LESSON TERKUNCI</span><h1>Satu langkah dulu!</h1><p>Selesaikan lesson sebelumnya di jalur belajarmu untuk membuka materi ini.</p><Link href="/learn" className="primary-button">KEMBALI KE JALUR <ArrowRight size={17}/></Link></div></div>;

  const exercise = lesson.exercises[step];
  const advance = () => {
    if (step === lesson.exercises.length - 1) {
      const perfect = wrongAnswers === 0 && result === 'success';
      const earned = alreadyCompleted ? 0 : mockGamificationService.completeLesson(perfect);
      finishLesson(lesson.id, earned);
      setXpEarned(earned);
      setComplete(true);
      return;
    }
    setStep(value => value + 1);
    setChoice(null);
    setChecked(false);
    setResult(null);
  };
  const checkAnswer = () => {
    if (choice !== exercise.answer) setWrongAnswers(value => value + 1);
    setChecked(true);
  };
  const startScan = () => {
    if (scanning) return;
    setScanning(true);
    setResult(null);
    window.setTimeout(() => {
      setScanning(false);
      const detected = Math.random() < 0.82 ? 'success' : 'retry';
      setResult(detected);
      if (detected === 'retry') setWrongAnswers(value => value + 1);
    }, 1600);
  };

  if (complete) {
    const accuracy = result === 'success' ? (wrongAnswers === 0 ? 100 : 92) : 58;
    return <div className="focus-layout"><div className="result-screen"><div className="result-confetti">✦　✧　✦</div><div className="result-medal">🏆</div><span className="eyebrow mint-eyebrow">SATU LANGKAH LAGI!</span><h1>{alreadyCompleted ? 'Lesson selesai lagi!' : 'Hebat, kamu berhasil!'}</h1><p>Lesson <b>{lesson.title}</b> selesai. {alreadyCompleted ? 'Kamu sudah mendapat XP dari lesson ini sebelumnya.' : 'Teruskan perjalanan belajarmu.'}</p><div className="reward-grid"><div><span className="reward-icon">⭐</span><b>{xpEarned ? `+${xpEarned}` : '0'} XP</b><small>{xpEarned ? 'XP diperoleh' : 'XP sudah diperoleh sebelumnya'}</small></div><div><span className="reward-icon">🎯</span><b>{accuracy}%</b><small>Akurasi latihan</small></div><div><span className="reward-icon">🔥</span><b>{profile.currentStreak} hari</b><small>Streak saat ini</small></div></div><div className="total-xp-card"><span>Total XP</span><b>{profile.totalXp} XP</b></div><div className="next-unlock"><span>🔓</span><div><b>{alreadyCompleted ? 'Terus pertahankan progres!' : 'Lesson berikutnya terbuka!'}</b><small>Jalur belajarmu terus bertambah.</small></div><Sparkles size={19}/></div><button className="primary-button" onClick={() => router.push('/learn')}>LANJUTKAN <ArrowRight size={18}/></button></div></div>;
  }

  return <main className="focus-layout"><header className="lesson-header"><Link aria-label="Keluar dari lesson" href="/learn" className="exit-lesson"><X size={21}/></Link><div className="lesson-progress"><div><i style={{ width: `${((step + 1) / lesson.exercises.length) * 100}%` }}/></div></div><span className="step-count">{step + 1} <small>/ {lesson.exercises.length}</small></span></header><section className="exercise-card"><div className="exercise-kicker"><span className="activity-badge">{exercise.kind === 'practice' ? <Camera size={17}/> : exercise.kind === 'info' ? <Hand size={17}/> : <Star size={16}/>}</span><span>{exercise.kind === 'practice' ? 'AI PRACTICE · DEMO' : exercise.kind === 'info' ? 'KENALI MATERI' : exercise.kind === 'matching' ? 'COCOKKAN GESTURE' : 'LATIHAN'}</span><span className="exercise-xp">+{lesson.xp} XP</span></div><h1>{exercise.prompt}</h1>

    {exercise.kind === 'info' && <><div className="gesture-demo"><div className="gesture-hand">{exercise.answer === 'A' ? '✊🏻' : exercise.answer === 'B' ? '🖐🏻' : '🤟🏻'}</div><span className="gesture-letter">{exercise.answer}</span><span className="gesture-caption">ILUSTRASI GESTURE · {profile.system}</span></div><p className="exercise-detail">{exercise.detail}</p><button className="primary-button exercise-submit" onClick={advance}>LANJUTKAN <ArrowRight size={18}/></button></>}

    {exercise.kind === 'practice' && <><div className="practice-target"><span>TARGET GESTURE</span><b>{exercise.answer}</b><small>{profile.system} · {lesson.subtitle}</small></div><div className={`camera-preview ${scanning ? 'scanning' : ''}`}><div className="camera-frame"><span className="corner tl"/><span className="corner tr"/><span className="corner bl"/><span className="corner br"/><div className="camera-hands">{scanning ? '🔎' : '✋🏻'}</div><span className="camera-label">{scanning ? 'MENGANALISIS GESTURE...' : 'AREA KAMERA · DEMO'}</span></div></div>{result && <div className={`practice-feedback ${result}`}><b>{result === 'success' ? 'Gesture dikenali ✓' : 'Gesture belum tepat'}</b><div className="accuracy-row">Akurasi <span>{result === 'success' ? '92%' : '58%'}</span></div><div className="accuracy-track"><i style={{ width: result === 'success' ? '92%' : '58%' }}/></div><small>{result === 'success' ? 'Posisi tangan sesuai · Gerakan stabil' : 'Coba arahkan tangan ke depan kamera.'}</small></div>}{!result ? <button className="primary-button exercise-submit" onClick={startScan} disabled={scanning}><Camera size={18}/>{scanning ? 'MENDETEKSI...' : 'MULAI DETEKSI'}</button> : <div className="practice-actions"><button className="secondary-button" onClick={startScan}><RotateCcw size={16}/> COBA LAGI</button><button className="primary-button" onClick={advance}>LANJUTKAN <ArrowRight size={18}/></button></div>}<p className="demo-caption">Simulasi AI untuk demo · kamera asli tidak digunakan</p></>}

    {(exercise.kind === 'choice' || exercise.kind === 'select' || exercise.kind === 'matching') && <>{exercise.kind === 'matching' && <div className="matching-prompt"><span>✋🏻</span><b>Gesture {exercise.answer}</b><span className="match-arrow">→</span><small>Pilih huruf yang sesuai</small></div>}{exercise.kind === 'select' && <div className="select-gesture-note">Pilih satu kartu gesture untuk melihat jawabannya.</div>}<div className="answer-grid">{(exercise.choices ?? []).map((item, index) => <button key={item} onClick={() => { setChoice(item); setChecked(false); }} className={`answer-option ${choice === item ? 'selected' : ''} ${checked && item === exercise.answer ? 'correct' : ''} ${checked && choice === item && item !== exercise.answer ? 'incorrect' : ''}`}><span className="choice-letter">{String.fromCharCode(65 + index)}</span>{item}</button>)}</div>{checked && <p className={`answer-feedback ${choice === exercise.answer ? 'good' : 'bad'}`}>{choice === exercise.answer ? 'Tepat sekali! Kamu makin mengenal gesture ini.' : 'Belum tepat. Coba ingat kembali bentuk gesture-nya.'}</p>}<button className="primary-button exercise-submit" disabled={!choice || checked} onClick={checkAnswer}>PERIKSA JAWABAN <Check size={18}/></button>{checked && <button className="text-button continue-answer" onClick={advance}>LANJUTKAN <ArrowRight size={16}/></button>}</>}
    </section><footer className="lesson-footnote">✦ Belajar dengan ritmemu sendiri. Kamu pasti bisa!</footer></main>;
}
