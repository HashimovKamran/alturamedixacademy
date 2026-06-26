import React,{useEffect,useState}from'react';

const root=()=>document.getElementById('altura-page-builder-root');
const csrf=()=>document.querySelector('meta[name="csrf-token"]')?.content||'';

export default function AssetPicker({value,onChange,disabled=false,returnUrl=false}){
 const [open,setOpen]=useState(false),[assets,setAssets]=useState([]),[loading,setLoading]=useState(false),[error,setError]=useState('');
 const api=async(path,options={})=>{const r=await fetch(root().dataset.apiBase+path,{credentials:'same-origin',headers:{Accept:'application/json','X-CSRF-TOKEN':csrf(),...options.headers},...options});const j=r.status===204?null:await r.json().catch(()=>({}));if(!r.ok)throw Error(j.message||'Media əməliyyatı alınmadı.');return j.data};
 const load=async()=>{setLoading(true);setError('');try{const response=await api('/assets?page=1');setAssets(response.data||[])}catch(e){setError(e.message)}finally{setLoading(false)}};
 useEffect(()=>{if(open)void load()},[open]);
 const upload=async event=>{const file=event.target.files?.[0];if(!file)return;const body=new FormData();body.append('file',file);try{const asset=await api('/assets',{method:'POST',body});setAssets(items=>[asset,...items]);onChange(returnUrl?asset.url:asset.id)}catch(e){setError(e.message)}};
 const choose=asset=>{onChange(returnUrl?asset.url:asset.id);setOpen(false)};
 const selected=assets.find(asset=>String(returnUrl?asset.url:asset.id)===String(value));
 return <><button type="button" className="apb-media-trigger" disabled={disabled} onClick={()=>setOpen(true)}>{selected?selected.original_name:(value?`Media #${value}`:'Media seç')}</button>{open&&<div className="apb-modal-backdrop"><div className="apb-modal"><header><b>Media kitabxanası</b><button type="button" onClick={()=>setOpen(false)}>×</button></header><label className="apb-upload">Şəkil yüklə<input type="file" accept="image/jpeg,image/png,image/webp,image/gif" onChange={upload}/></label>{loading?<p>Yüklənir…</p>:<div className="apb-media-grid">{assets.map(asset=><button type="button" key={asset.id} className="apb-media-card" onClick={()=>choose(asset)}><img src={asset.url} alt={asset.alt_text||''}/><small>{asset.original_name}</small></button>)}</div>}{error&&<p className="apb-error">{error}</p>}</div></div>}</>;
}
