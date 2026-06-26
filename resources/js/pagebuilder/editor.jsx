import React,{useEffect,useState}from'react';
import { createRoot } from 'react-dom/client';
import Workspace from './workspace.jsx';

const editor=document.getElementById('altura-page-builder-root');
const canvas=document.getElementById('altura-page-builder-canvas');

function Preview(){const[state,setState]=useState(null);useEffect(()=>{const receive=e=>{if(e.origin!==location.origin||e.source!==parent||e.data?.channel!=='altura-page-builder')return;if(e.data.type==='render')setState(e.data.payload)};addEventListener('message',receive);parent.postMessage({channel:'altura-page-builder',type:'ready'},location.origin);return()=>removeEventListener('message',receive)},[]);if(!state)return <div/>;const maps=[state.document.layout.header,{sections:state.document.sections,order:state.document.order},state.document.layout.footer];return <>{maps.map((map,i)=><Zone key={i} map={map}/>)}</>}
function Zone({map}){return <>{(map?.order||[]).map(id=><Node key={id} node={map.sections[id]}/>)}</>}
function Node({node}){if(!node||node.disabled)return null;const s=node.settings||{};return <section className="apb-preview-node"><strong>{s.title||node.type}</strong>{s.text&&<p>{s.text}</p>}{(node.order||[]).map(id=><Node key={id} node={node.blocks[id]}/>)}</section>}

if(editor)createRoot(editor).render(<Workspace mount={editor}/>);
if(canvas)createRoot(canvas).render(<Preview/>);
