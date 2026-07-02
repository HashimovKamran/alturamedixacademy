import React,{useEffect,useState}from'react';

const root=()=>document.getElementById('altura-page-builder-root');
const csrf=()=>document.querySelector('meta[name="csrf-token"]')?.content||'';

export default function AssetPicker({value,onChange,disabled=false,returnUrl=false}){
 const [open,setOpen]=useState(false),[assets,setAssets]=useState([]),[page,setPage]=useState(1),[hasMore,setHasMore]=useState(false),[loading,setLoading]=useState(false),[error,setError]=useState('');
 const api=async(path,options={})=>{const response=await fetch(root().dataset.apiBase+path,{credentials:'same-origin',headers:{Accept:'application/json','X-CSRF-TOKEN':csrf(),...options.headers},...options});const body=response.status===204?null:await response.json().catch(()=>({}));if(!response.ok)throw Error(body?.message||'Media əməliyyatı alınmadı.');return body?.data};
 const load=async(nextPage=1,append=false)=>{setLoading(true);setError('');try{const response=await api(`/assets?page=${nextPage}&per_page=36`);setAssets(items=>append?[...items,...(response.data||[])]:response.data||[]);setPage(nextPage);setHasMore(Boolean(response.meta?.has_more))}catch(error){setError(error.message)}finally{setLoading(false)}};
 useEffect(()=>{if(open)void load(1)},[open]);
 const upload=async event=>{const file=event.target.files?.[0];if(!file)return;const body=new FormData();body.append('file',file);try{const asset=await api('/assets',{method:'POST',body});setAssets(items=>[asset,...items]);onChange(returnUrl?asset.url:asset.id)}catch(error){setError(error.message)}};
 const choose=asset=>{onChange(returnUrl?asset.url:asset.id);setOpen(false)};
 const selected=assets.find(asset=>String(returnUrl?asset.url:asset.id)===String(value));
 return <><button type="button" className="apb-media-trigger" disabled={disabled} onClick={()=>setOpen(true)}>{selected?selected.original_name:(value?`Media #${value}`:'Media seç')}</button>{open&&<div className="apb-modal-backdrop"><div className="apb-modal"><header><b>Media kitabxanası</b><button type="button" onClick={()=>setOpen(false)}>×</button></header><label className="apb-upload">Şəkil yüklə<input type="file" accept="image/jpeg,image/png,image/webp,image/gif" onChange={upload}/></label>{loading&&assets.length===0?<p>Yüklənir…</p>:<><div className="apb-media-grid">{assets.map(asset=><button type="button" key={asset.id} className="apb-media-card" onClick={()=>choose(asset)}><img src={asset.url} alt={asset.alt_text||''}/><small>{asset.original_name}</small></button>)}</div>{hasMore&&<button type="button" className="apb-load-more" disabled={loading} onClick={()=>void load(page+1,true)}>{loading?'Yüklənir…':'Daha çox göstər'}</button>}</>}{error&&<p className="apb-error">{error}</p>}</div></div>}</>;
}
