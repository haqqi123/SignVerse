'use client';
import { useState } from 'react';
import { Check, Hand, RotateCcw, Sparkles, Target } from 'lucide-react';
import { TopStats } from '@/components/layout/TopStats';
import { useDemo } from '@/components/providers/DemoProvider';
import { xpRules } from '@/data/gamification';

const targets = [
  { letter: 'A', hand: '✊', hint: 'Kepalkan tangan, ibu jari berada di samping.' },
  { letter: 'B', hand: '🖐🏻', hint: 'Rapatkan empat jari, tekuk ibu jari.' },
  { letter: 'C', hand: '🤏🏻', hint: 'Lengkungkan jari dan ibu jari membentuk huruf C.' },
  { letter: 'D', hand: '☝🏻', hint: 'Angkat telunjuk, jari lain menyentuh ibu jari.' },
  { letter: 'E', hand: '✊🏻', hint: 'Tekuk semua jari ke arah telapak tangan.' },
];

export function PracticeExperience() {
  const { profile, finishPractice } = useDemo();
  const [selected, setSelected] = useState(0);
  const [scanning, setScanning] = useState(false);
  const [result, setResult] = useState<'success' | 'retry' | null>(null);
  const [earnedOnThisRun, setEarnedOnThisRun] = useState(false);
  const target = targets[selected];
  const practiced = new Set(profile.completedPractices);

  function choose(index: number) {
    setSelected(index);
    setResult(null);
    setEarnedOnThisRun(false);
  }

  function detect() {
    if (scanning) return;
    setScanning(true);
    setResult(null);
    window.setTimeout(() => {
      const success = Math.random() < 0.82;
      const id = `practice-${profile.system}-${target.letter}`;
      setResult(success ? 'success' : 'retry');
      setScanning(false);
      if (success) {
        setEarnedOnThisRun(!practiced.has(id));
        finishPractice(id, practiced.has(id) ? 0 : xpRules.aiPractice);
      }
    }, 1300);
  }

  return <><TopStats/><main className="practice-page practice-focused">
    <header className="practice-page-heading">
      <span className="eyebrow">AI PRACTICE · {profile.system}</span>
      <h1>Latih gesture</h1>
      <p>Pilih satu huruf, ikuti panduan, lalu cocokkan bentuk tanganmu.</p>
    </header>

    <div className="practice-dashboard-grid">
      <section className="practice-console card" aria-label="Latihan gesture">
        <div className="practice-console-heading">
          <div><span className="eyebrow">SESI GESTURE</span><h2>Latihan mandiri</h2></div>
          <span className="session-system">{profile.system}</span>
        </div>

        <div className="practice-target-select">
          <div><b>Pilih target</b><small>Mulai dari alfabet dasar</small></div>
          <div className="target-pills" role="group" aria-label="Pilih target huruf">
            {targets.map((item, index) => <button key={item.letter} type="button" aria-label={`Target ${item.letter}`} aria-pressed={selected === index} className={`target-pill ${selected === index ? 'selected' : ''} ${practiced.has(`practice-${profile.system}-${item.letter}`) ? 'practiced' : ''}`} onClick={() => choose(index)}><span>{item.hand}</span><b>{item.letter}</b>{practiced.has(`practice-${profile.system}-${item.letter}`) && <Check size={12}/>}</button>)}
          </div>
        </div>

        <div className={`practice-studio ${scanning ? 'is-scanning' : ''}`}>
          <div className="studio-topline"><span><i/>{scanning ? 'MENGANALISIS GESTURE' : 'PANDUAN GESTURE'}</span><span>{profile.system}</span></div>
          <div className="studio-frame">
            <div className="studio-guides"><i/><i/><i/></div>
            <span className="studio-hand" aria-hidden="true">{target.hand}</span>
            <div className="studio-target-label"><small>TARGET</small><b>{target.letter}</b></div>
            <div className="studio-status">AREA LATIHAN · SIMULASI TANPA KAMERA</div>
            <i className="studio-corner top-left"/><i className="studio-corner top-right"/><i className="studio-corner bottom-left"/><i className="studio-corner bottom-right"/>
          </div>
          <p className="studio-hint">{target.hint}</p>
        </div>

        {result && <div className={`studio-result ${result === 'retry' ? 'retry' : ''}`} role="status">
          <div className="result-title"><span>{result === 'success' ? <Check size={16}/> : <Hand size={16}/>}</span><div><b>{result === 'success' ? 'Gesture cocok!' : 'Coba sekali lagi'}</b><small>{result === 'success' ? 'Bagus, bentuk tanganmu sesuai target.' : 'Perhatikan posisi jari lalu ulangi.'}</small></div>{result === 'success' && <strong>+{earnedOnThisRun ? xpRules.aiPractice : 0} XP</strong>}</div>
          {result === 'success' && <div className="practice-reward-line">{earnedOnThisRun ? 'Huruf ini tercatat di progres alfabetmu.' : 'Huruf ini sudah pernah dicatat.'}</div>}
        </div>}

        <div className="practice-control-row">
          {result === 'retry' ? <button type="button" className="secondary-button" onClick={detect}><RotateCcw size={15}/> Coba lagi</button> : null}
          <button type="button" className="primary-button" disabled={scanning} onClick={detect}>{scanning ? <><Sparkles size={15}/> Menganalisis…</> : <><Target size={15}/> Periksa gesture</>}</button>
        </div>
      </section>

      <aside className="practice-progress-card card">
        <span className="eyebrow">PROGRES ALFABET</span>
        <div className="practice-progress-total"><b>{targets.filter(item => practiced.has(`practice-${profile.system}-${item.letter}`)).length}</b><span> dari {targets.length} huruf</span></div>
        <div className="progress-track"><i style={{ width: `${targets.filter(item => practiced.has(`practice-${profile.system}-${item.letter}`)).length / targets.length * 100}%` }}/></div>
        <div className="practice-letter-list">{targets.map(item => { const complete = practiced.has(`practice-${profile.system}-${item.letter}`); return <div key={item.letter} className={complete ? 'complete' : ''}><span>{item.letter}</span><small>{complete ? 'Sudah dicoba' : 'Belum dicoba'}</small>{complete && <Check size={14}/>}</div>; })}</div>
      </aside>
    </div>
  </main></>;
}
