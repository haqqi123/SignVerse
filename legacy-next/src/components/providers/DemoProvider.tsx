'use client';
import { createContext, useContext, useEffect, useState } from 'react';
import type { GamificationProfile, SignSystem } from '@/types';
type DemoContextValue = { profile: GamificationProfile; ready: boolean; setSystem: (s: SignSystem) => void; setDisplayName: (name: string) => void; finishLesson: (id: string, xp: number) => void; finishPractice: (id: string, xp: number) => void; finishChallenge: () => void; reset: () => void };
const today = () => new Date().toISOString().slice(0, 10);
const initial: GamificationProfile = { totalXp: 80, currentStreak: 5, longestStreak: 8, completedLessons: [], completedPractices: [], challengeProgress: 3, challengeClaimed: false, lastActiveDate: today(), displayName: 'Aji Pratama', activityByDate: {}, system: 'SIBI' };
const Context = createContext<DemoContextValue | null>(null);
export function DemoProvider({ children }: { children: React.ReactNode }) {
 const [profile,setProfile]=useState(initial); const [ready,setReady]=useState(false);
 useEffect(()=>{ try { const saved=localStorage.getItem('signteach-demo'); if(saved) setProfile({...initial,...JSON.parse(saved)}); } catch {} setReady(true); },[]);
 useEffect(()=>{ if(ready) localStorage.setItem('signteach-demo',JSON.stringify(profile)); },[profile,ready]);
 const recordActivity=(p:GamificationProfile,xp:number):GamificationProfile=>{const now=today();const gap=Math.round((Date.parse(`${now}T00:00:00Z`)-Date.parse(`${p.lastActiveDate}T00:00:00Z`))/86400000);const streak=gap===0?p.currentStreak:gap===1?p.currentStreak+1:1;return {...p,totalXp:p.totalXp+xp,currentStreak:streak,longestStreak:Math.max(p.longestStreak,streak),lastActiveDate:now,activityByDate:{...p.activityByDate,[now]:(p.activityByDate[now]??0)+1}};};
 const value:DemoContextValue={profile,ready,setSystem:(system)=>setProfile(p=>({...p,system})),setDisplayName:(displayName)=>setProfile(p=>({...p,displayName:displayName.trim()||'Aji Pratama'})),finishLesson:(id,xp)=>setProfile(p=>{if(p.completedLessons.includes(id))return p;return {...recordActivity(p,xp),completedLessons:[...p.completedLessons,id],challengeProgress:Math.min(5,p.challengeProgress+1)};}),finishPractice:(id,xp)=>setProfile(p=>{if(p.completedPractices.includes(id))return p;return {...recordActivity(p,xp),completedPractices:[...p.completedPractices,id],challengeProgress:Math.min(5,p.challengeProgress+1)};}),finishChallenge:()=>setProfile(p=>p.challengeProgress<5||p.challengeClaimed?p:{...p,totalXp:p.totalXp+20,challengeClaimed:true}),reset:()=>setProfile(initial)};
 return <Context.Provider value={value}>{children}</Context.Provider>;
}
export function useDemo(){const context=useContext(Context);if(!context)throw new Error('useDemo must be used within DemoProvider');return context;}
