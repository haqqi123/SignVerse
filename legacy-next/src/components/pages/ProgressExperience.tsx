'use client';
import Link from 'next/link';
import { ArrowRight, BookOpen, Check, Flame, Gauge, Star, Target, Trophy } from 'lucide-react';
import { TopStats } from '@/components/layout/TopStats';
import { useDemo } from '@/components/providers/DemoProvider';
import { levels } from '@/data/gamification';
import { lessonsBySystem } from '@/data/lessons';

const weekdays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
export function ProgressExperience() {
  const { profile } = useDemo();
  const lessons = lessonsBySystem[profile.system];
  const done = lessons.filter(item => profile.completedLessons.includes(item.id));
  const nextLesson = lessons.find(item => !profile.completedLessons.includes(item.id));
  const level = levels.find(item => profile.totalXp < item.max) ?? levels[levels.length - 1];
  const nextLevel = levels[levels.indexOf(level) + 1];
  const completion = Math.round(done.length / lessons.length * 100);
  const xpInLevel = profile.totalXp - level.min;
  const xpRange = nextLevel ? nextLevel.min - level.min : 100;
  const week = Array.from({ length: 7 }, (_, i) => {
    const day = new Date(); day.setUTCHours(0, 0, 0, 0); day.setUTCDate(day.getUTCDate() - (6 - i));
    const key = day.toISOString().slice(0, 10);
    return { label: weekdays[day.getUTCDay()], count: profile.activityByDate[key] ?? 0, today: i === 6 };
  });
  const maxCount = Math.max(1, ...week.map(day => day.count));
  const activeDays = week.filter(day => day.count > 0).length;
  const averageAccuracy = done.length || profile.completedPractices.length ? 92 : 0;

  return <><TopStats/><div className="progress-page">
    <section className="progress-hero"><div><span className="eyebrow mint-eyebrow">RINGKASAN BELAJAR · {profile.system}</span><h1>Kamu sedang<br/><span>bertumbuh hebat.</span></h1><p>Setiap latihan menambah cara baru untuk terhubung.</p><div className="progress-hero-level"><span className="level-star">✦</span><div><b>{level.name}</b><small>{nextLevel ? `${nextLevel.min - profile.totalXp} XP menuju ${nextLevel.name}` : 'Level tertinggi!'}</small></div><strong>{xpInLevel}<small> / {xpRange} XP</small></strong></div><div className="level-track"><i style={{ width: `${Math.min(100, xpInLevel / xpRange * 100)}%` }}/></div></div><div className="course-donut" style={{ background: `conic-gradient(#31a982 0% ${completion}%, #e7eee8 ${completion}% 100%)` }}><div><b>{completion}<small>%</small></b><span>jalur selesai</span></div></div></section>

    <section className="progress-stat-grid"><article className="progress-stat-card card xp"><span className="stat-icon"><Star size={18}/></span><small>XP TERKUMPUL</small><b>{profile.totalXp}</b><span className="stat-caption">Dari aktivitas belajarmu</span></article><article className="progress-stat-card card lessons"><span className="stat-icon"><BookOpen size={18}/></span><small>LESSON SELESAI</small><b>{done.length}<i>/{lessons.length}</i></b><span className="stat-caption">Terus buka jalur baru</span></article><article className="progress-stat-card card streak"><span className="stat-icon"><Flame size={18}/></span><small>STREAK SAAT INI</small><b>{profile.currentStreak}<i> hari</i></b><span className="stat-caption">Terpanjang: {profile.longestStreak} hari</span></article><article className="progress-stat-card card accuracy"><span className="stat-icon"><Gauge size={18}/></span><small>AKURASI LATIHAN</small><b>{averageAccuracy ? `${averageAccuracy}%` : '—'}</b><span className="stat-caption">{averageAccuracy ? 'Rata-rata hasil mock AI' : 'Selesaikan practice pertama'}</span></article></section>

    <div className="progress-detail-grid"><section className="activity-card card"><div className="section-title-row"><div><span className="eyebrow">RITME MINGGU INI</span><h2>Aktivitas belajarmu</h2><p>{activeDays ? `${activeDays} hari aktif · ${week.reduce((sum, day) => sum + day.count, 0)} sesi selesai` : 'Mulai belajar untuk mengisi perjalananmu.'}</p></div><span className="activity-bubble">📅</span></div><div className="activity-bars">{week.map((day, index) => <div key={`${day.label}-${index}`} className={`activity-day ${day.today ? 'today' : ''}`}><div className="activity-bar-back"><i style={{ height: day.count ? `${Math.max(14, day.count / maxCount * 100)}%` : '0%' }}/></div><small>{day.label}</small>{day.today && <span className="today-label">HARI INI</span>}</div>)}</div><div className="activity-foot"><span><i/> Aktivitas lesson</span><small>Data tersimpan lokal di demo</small></div></section>

      <section className="consistency-card card"><span className="eyebrow">TARGET HARIAN</span><div className="consistency-icon">🎯</div><h2>{profile.challengeClaimed ? 'Tantangan selesai!' : 'Praktikkan 5 gesture'}</h2><p>{profile.challengeClaimed ? 'Kamu sudah mengklaim hadiah hari ini.' : 'Setiap lesson selesai membantu mencapai target.'}</p><div className="challenge-meter-label"><b>{profile.challengeProgress} / 5</b><span>gesture</span></div><div className="progress-track"><i style={{ width: `${profile.challengeProgress * 20}%` }}/></div><div className="consistency-reward"><span>Hadiah tantangan</span><b>⭐ +20 XP</b></div><Link href="/challenge" className="consistency-link">Lihat tantangan <ArrowRight size={15}/></Link></section></div>

    <section className="progress-path-card card"><div className="section-title-row"><div><span className="eyebrow">LANJUTKAN PERJALANAN</span><h2>Jalur {profile.system}</h2><p>{done.length} dari {lessons.length} lesson sudah kamu selesaikan.</p></div><Link href="/learn" className="path-all-link">Lihat semua <ArrowRight size={15}/></Link></div><div className="mini-path-list">{lessons.slice(0, 4).map((lesson, index) => { const complete = profile.completedLessons.includes(lesson.id); const isNext = lesson.id === nextLesson?.id; return <div className={`mini-path-item ${complete ? 'complete' : ''} ${isNext ? 'next' : ''}`} key={lesson.id}><span className="mini-path-node">{complete ? <Check size={14}/> : isNext ? <Target size={14}/> : <span>{index + 1}</span>}</span><div><b>{lesson.title}</b><small>{complete ? 'Selesai' : isNext ? 'Lesson berikutnya' : 'Terkunci'}</small></div><span className="mini-path-xp">{complete ? '✓' : `+${lesson.xp} XP`}</span></div>; })}</div>{nextLesson ? <Link href={`/lesson/${nextLesson.id}`} className="primary-button progress-continue">Lanjutkan lesson <ArrowRight size={17}/></Link> : <div className="course-complete"><Trophy size={17}/> Semua lesson selesai! Pilih BISINDO untuk menjelajah lebih jauh.</div>}</section>

    <div className="progress-note"><Target size={15}/> Angka akurasi adalah contoh respons mock dan akan berasal dari API saat backend tersedia.</div>
  </div></>;
}
