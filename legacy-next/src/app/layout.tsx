import type { Metadata } from 'next'; import { DemoProvider } from '@/components/providers/DemoProvider'; import './globals.css'; import './responsive.css';
export const metadata: Metadata = { title: 'SignTeach — Belajar Bahasa Isyarat', description: 'Belajar SIBI dan BISINDO dengan jalur belajar interaktif.' };
export default function RootLayout({children}:{children:React.ReactNode}){return <html lang="id"><body><DemoProvider>{children}</DemoProvider></body></html>}
