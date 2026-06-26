import React,{useEffect,useState}from'react';
import{createRoot}from'react-dom/client';
const mount=document.getElementById('altura-page-builder-canvas');
function Canvas(){const[state,setState]=useState(null);useEffect(()=>{const receive=e=>{if(e.origin!==location.origin||e.source!==parent||e.data?.channel!=='altura-page-builder')return;if(e.data.type==='render')setState(e.data.payload)};addEventListener('message',receive);parent.postMessage({channel:'altura-page-builder',type:'ready'},location.origin);return()=>removeEventListener('message',receive)},[]);if(!state)return <div/>;const main={sections:state.document.sections,order:state.document.order};return <>{[state.document.layout.header,main,state.document.layout.footer].map((map,index)=><Zone key={index} map={map}/>)}</>}
function Zone({map}){return <>{(map?.order||[]).map(id=><Item key={id} node={map.sections[id]}/>)}</>}
function Item({node}){if(!node||node.disabled)return null;const settings=node.settings||{};return <section className="apb-preview-node"><b>{settings.title||node.type}</b>{settings.text&&<p>{settings.text}</p>}{node.order.map(id=><Item key={id} node={node.blocks[id]}/>)}</section>}
if(mount)createRoot(mount).render(<Canvas/>);
