import { LessonRunner } from '@/components/lesson/LessonRunner';
export function generateStaticParams(){return ['alphabet-a','alphabet-b','numbers','words','sentences','school','daily','bisindo-alphabet-a','bisindo-alphabet-b','bisindo-numbers','bisindo-words','bisindo-sentences','bisindo-school','bisindo-daily'].map(id=>({id}))}
export default async function Page({params}:{params:Promise<{id:string}>}){const {id}=await params;return <LessonRunner id={id}/>}
