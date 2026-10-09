'use client';
import { useState } from 'react';
import { ArrowRight, Check, LockKeyhole, Sparkles, Trophy } from 'lucide-react';
import Link from 'next/link';
import { TopStats } from '@/components/layout/TopStats';
import { useDemo } from '@/components/providers/DemoProvider';
import { achievements, levels } from '@/data/gamification';
import { lessonsBySystem } from '@/data/lessons';

type Filter = 'all' | 'earned' | 'locked';
export function AchievementExperience() {
  const { profile } = useDemo();
  const [filter, setFilter] = useState<Filter>('all');
  const lessonCount = profile.completedLessons.filter(id => lessonsBySystem[profile.system].some(lesson => lesson.id === id)).length;
  const currentLevel = levels.find(item => profile.totalXp < item.max) ?? levels[levels.length - 1];
  const nextLevel = levels[levels.indexOf(currentLevel) + 1];
  const valueFor = (id: string) => {
    if (id === 'first-step' || id === 'alphabet-pro' || id === 'pathfinder') return lessonCount;
    if (id === 'steady' || id === 'streak-seven') return profile.currentStreak;
    if (id === 'explorer' || id === 'communicator') return profile.totalXp;
    if (id === 'first-practice') return profile.completedPractices.length;
    return 0;
  };
  const items = achievements.map(item => ({ ...item, progress: Math.min(item.requirement, valueFor(item.id)), earned: valueFor(item.id) >= item.requirement }));
  const earned = items.filter(item => item.earned).length;
  const visible = items.filter(item => filter === 'all' || (filter === 'earned' ? item.earned : !item.earned));
  const levelProgress = nextLevel ? Math.min(100, (profile.totalXp - currentLevel.min) / (nextLevel.min - currentLevel.min) * 100) : 100;

  return <><TopStats/><div className="achievement-page"><section className="achievement-hero"><div className="achievement-hero-copy"><span className="eyebrow mint-eyebrow">KOLEKSI PENCAPAIAN</span><h1>Setiap langkah<br/><span>pantas dirayakan.</span></h1><p>Kumpulkan lencana dari usaha, konsistensi, dan keberanianmu mencoba hal baru.</p><div className="achievement-hero-stats"><div><b>{earned}<small> / {items.length}</small></b><span>Lencana terbuka</span></div><i/><div><b>{lessonCount}<small> lesson</small></b><span>Sudah diselesaikan</span></div></div></div><div className="achievement-trophy-scene"><span className="trophy-ring ring-a"/><span className="trophy-ring ring-b"/><div className="trophy-pedestal"><Trophy size={53}/><Sparkles className="trophy-sparkle" size={19}/></div><span className="trophy-tag">LEVEL {currentLevel.name.toUpperCase()}</span></div></section>

    <section className="badge-progress-banner"><div className="badge-progress-icon">✦</div><div className="badge-progress-copy"><b>{nextLevel ? `${nextLevel.min - profile.totalXp} XP lagi ke ${nextLevel.name}` : 'Kamu mencapai level tertinggi!'}</b><small>Terus belajar untuk membuka lencana dan level berikutnya.</small><div className="badge-level-track"><i style={{ width: `${levelProgress}%` }}/></div></div><div className="badge-progress-value">{profile.totalXp}<small> / {nextLevel?.min ?? currentLevel.min} XP</small></div></section>

    <div className="achievement-collection-heading"><div><span className="eyebrow">PERJALANANMU</span><h2>Koleksi lencana</h2><p>Lencana terbuka dan target yang sedang kamu kejar.</p></div><div className="achievement-filter" role="group" aria-label="Filter pencapaian">{([{ key: 'all', label: 'Semua' }, { key: 'earned', label: 'Terbuka' }, { key: 'locked', label: 'Belum terbuka' }] as const).map(option => <button key={option.key} aria-pressed={filter === option.key} onClick={() => setFilter(option.key)} className={filter === option.key ? 'active' : ''}>{option.label}</button>)}</div></div>

    {visible.length ? <div className="achievement-collection">{visible.map((item, index) => { const percentage = Math.round(item.progress / item.requirement * 100); return <article key={item.id} className={`badge-card card ${item.earned ? 'unlocked' : ''}`}><div className={`badge-medal ${item.earned ? 'shiny' : ''}`}><span>{item.icon}</span>{item.earned ? <i><Check size={11}/></i> : <i><LockKeyhole size={10}/></i>}</div><div className="badge-card-copy"><div className="badge-card-top"><span className="eyebrow">{item.earned ? 'LENCANA TERBUKA' : `MILESTONE 0${index + 1}`}</span><span className="badge-points">{item.earned ? '✦ TERCAPAI' : `${percentage}%`}</span></div><h3>{item.title}</h3><p>{item.description}</p><div className="badge-meter"><i style={{ width: `${percentage}%` }}/></div><small>{item.earned ? 'Kamu berhasil membuka lencana ini!' : `${item.progress} / ${item.requirement} menuju lencana`}</small></div></article>; })}</div> : <div className="achievement-empty card"><span>🏅</span><h3>{filter === 'earned' ? 'Lencana pertama menunggumu' : 'Semua milestone sudah tercapai!'}</h3><p>{filter === 'earned' ? 'Selesaikan satu lesson untuk mulai mengumpulkan lencana.' : 'Kamu luar biasa. Terus jaga semangat belajarmu.'}</p><Link href="/learn">Ke jalur belajar <ArrowRight size={15}/></Link></div>}
    <footer className="achievement-footnote">✦ Progres lencana ini tersimpan di perangkat untuk keperluan demo.</footer></div></>;
}
