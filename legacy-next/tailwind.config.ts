import type { Config } from 'tailwindcss';
const config: Config = { content: ['./src/**/*.{js,ts,jsx,tsx,mdx}'], theme: { extend: { colors: { ink: '#173b3b', teal: '#16877e', mint: '#e4f5ef', coral: '#fa8b70', cream: '#fbfaf6' }, boxShadow: { soft: '0 8px 30px rgba(21,60,57,.08)' } } }, plugins: [] };
export default config;
