import { Sidebar } from './Sidebar'; import { RightPanel } from './RightPanel';
export function AppShell({children}:{children:React.ReactNode}){return <div className="app-shell"><Sidebar/><main className="main-column">{children}</main><RightPanel/></div>}
