/* VOTESS shared behaviour: header, footer, sliders, cards, forms. Content lives in config.js */
const $=(s,r=document)=>r.querySelector(s),$$=(s,r=document)=>[...r.querySelectorAll(s)],RM=matchMedia('(prefers-reduced-motion:reduce)').matches,V=window.VOTESS;
const NT='target="_blank" rel="noopener noreferrer"';   // every page link opens in a new tab
const S='<svg viewBox="0 0 24 24" aria-hidden="true" ';
const IC={
 linkedin:S+'fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
 x:S+'fill="currentColor"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932 6.064-6.932zm-1.29 19.494h2.039L6.486 3.24H4.298l13.313 17.407z"/></svg>',
 facebook:S+'fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
 instagram:S+'fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>',
 youtube:S+'fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="4"/><path d="M10 9l5 3-5 3z" fill="currentColor"/></svg>',
 whatsapp:S+'fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M12 3a9 9 0 00-7.7 13.6L3 21l4.5-1.2A9 9 0 1012 3z"/><path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1-1.6-2-1-1 .9c-1-.4-2-1.4-2.4-2.4l.9-1-1-2z" fill="currentColor" stroke="none"/></svg>'};
const LB={linkedin:'LinkedIn',x:'X',facebook:'Facebook',instagram:'Instagram',youtube:'YouTube',whatsapp:'WhatsApp'};
const social=()=>Object.keys(IC).filter(k=>V.social[k]).map(k=>`<a href="${V.social[k]}" ${NT} aria-label="${LB[k]}" title="${LB[k]}">${IC[k]}</a>`).join('');
const GR=['linear-gradient(135deg,#86BC25,#26890D)','linear-gradient(135deg,#00A3E0,#0076A8)','linear-gradient(135deg,#53565A,#000)','linear-gradient(135deg,#0076A8,#012a3d)'];
const grad=t=>GR[[...t].reduce((a,c)=>a+c.charCodeAt(0),0)%GR.length];

/* header (hamburger toggle on mobile) */
(function(){const cur=location.pathname.split('/').pop()||'index.html';
 const li=m=>`<li><a class="t${m.cta?' cta':''}" href="${m.href}" ${NT}${cur===m.href?' aria-current="page"':''}>${m.label}</a>${m.children?`<div class="dd">${m.children.map(c=>`<a href="${c.href}" ${NT}>${c.label}<small>${c.d||''}</small></a>`).join('')}</div>`:''}</li>`;
 $('#hdr').outerHTML=`<header class="hd"><div class="wrap"><a class="logo" href="index.html"><img src="assets/logo-light.jpg?v=8" alt="VOTESS" class="logo-img logo-light"><img src="assets/logo-dark.jpg?v=8" alt="VOTESS" class="logo-img logo-dark"></a><button class="mb" id="mb" aria-expanded="false" aria-controls="nv" aria-label="Toggle menu"><i></i></button><nav id="nv" aria-label="Main"><ul>${V.menu.map(li).join('')}</ul><div class="nsoc"><span>Follow us</span><div class="sx">${social()}</div></div></nav></div></header>`;
 $('#mb').onclick=e=>{const o=$('#nv').classList.toggle('open');e.currentTarget.setAttribute('aria-expanded',o)};
 document.addEventListener('keydown',e=>{if(e.key==='Escape'){$('#nv').classList.remove('open');$('#mb').setAttribute('aria-expanded',false)}});
})();
/* footer with social icons */
(function(){const col=(h,l)=>`<div><h4>${h}</h4>${l.map(x=>`<a href="${x[1]}" ${NT}>${x[0]}</a>`).join('')}</div>`;
 $('#ftr').outerHTML=`<footer><div class="wrap"><div class="flogo"><a href="index.html"><img src="assets/logo-dark.jpg?v=8" alt="VOTESS" class="flogo-img"></a><p style="color:#BBBCBC;font-size:.88rem;max-width:28ch">Virtual Operating Technology &amp; Enterprise Systems</p></div><div class="fg">${col('Company',[['About us','about.html'],['Who we are','who-we-are.html'],['Our team','team.html'],['Careers','careers.html'],['Partners','partners.html'],['Contact','contact.html'],['Submit RFP','submit-rfp.html'],['Pay online','pay.html']])}${col('What we do',[['Digital transformation','what-we-do.html#digital'],['Enterprise software','what-we-do.html#software'],['Our products','what-we-do.html#products'],['Nayibeej','nayibeej.html']])}${col('Our thinking',[['Perspectives','our-thinking.html'],['Newsroom','newsroom.html'],['Events','events.html'],['Press releases','press-releases.html']])}<div><h4>Follow us</h4><div class="sx">${social()}</div></div></div><div class="fb"><span>&copy; 2026 VOTESS, Virtual Operating Technology &amp; Enterprise Systems. All rights reserved.</span><span class="fpol"><a href="privacy.html" ${NT}>Privacy</a> <a href="terms.html" ${NT}>Terms</a> <a href="cookies.html" ${NT}>Cookies</a> <a href="disclaimer.html" ${NT}>Disclaimer</a> <a href="refund-policy.html" ${NT}>Refund policy</a></span><span>Nayibeej is an independent non-profit supported by VOTESS. It is not a VOTESS business.</span></div></div></footer>`;})();

/* headline reveal, progress, scroll reveal */
$$('[data-split]').forEach(h=>{h.setAttribute('aria-label',h.textContent);h.innerHTML=h.textContent.split(' ').map((w,i)=>`<span class="w"><span class="wi" style="transition-delay:${.05+i*.1}s">${w}</span></span>`).join(' ')});
requestAnimationFrame(()=>requestAnimationFrame(()=>document.body.classList.add('go')));
const pr=$('#prog');if(pr)addEventListener('scroll',()=>{const h=document.documentElement;pr.style.transform=`scaleX(${scrollY/Math.max(1,h.scrollHeight-innerHeight)})`},{passive:true});
const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}}),{threshold:.12});
const reveal=()=>$$('.rv:not(.in)').forEach(e=>io.observe(e));

/* cards */
const card=x=>`<article class="cd rv"><i style="background:${grad(x.tag)}"></i><div><small>${x.tag}</small><h3>${x.title}</h3><p>${x.text}</p><span class="dt">${x.date}</span></div></article>`;
const prod=p=>`<article class="pc rv"><div class="top" style="background:${p.grad}"><small>${p.sector}</small><h3>${p.name}</h3></div><div class="bd"><span class="tg">${p.tag}</span><p>${p.desc}</p><ul>${p.feat.map(f=>`<li>${f}</li>`).join('')}<li class="more-li"><button type="button" class="btn-more-feat" onclick="openProductModal('${p.id}')">+ Other / Read more &rarr;</button></li></ul><div class="acts"><a class="btn sm" href="${p.url}" ${NT}>${p.cta}</a><a class="btn sm k" href="contact.html?purpose=${p.demo}" ${NT}>Talk to us</a></div></div></article>`;
$$('[data-products]').forEach(e=>e.innerHTML=V.products.map(prod).join(''));
/* product details modal */
window.openProductModal=function(id){
  const p=V.products.find(x=>x.id===id);if(!p)return;
  let m=$('#prod-modal');
  if(!m){
    m=document.createElement('div');m.id='prod-modal';m.className='pm-backdrop';
    document.body.appendChild(m);
    m.onclick=e=>{if(e.target===m)closeProductModal()};
  }
  const flist=(p.allFeat||p.feat).map(f=>`<li><svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg><span>${f}</span></li>`).join('');
  m.innerHTML=`<div class="pm-dialog" role="dialog" aria-modal="true" aria-labelledby="pm-title"><div class="pm-top" style="background:${p.grad}"><button class="pm-close" onclick="closeProductModal()" aria-label="Close modal">&times;</button><span class="pm-sec">${p.sector}</span><h2 id="pm-title">${p.name}</h2><span class="pm-tag">${p.tag}</span></div><div class="pm-body"><h3>Overview</h3><p>${p.fullDesc||p.desc}</p><h3>Core Modules &amp; Capabilities</h3><ul class="pm-features">${flist}</ul><div class="pm-acts"><a class="btn" href="${p.url}" ${NT}>${p.cta}</a><a class="btn o" href="contact.html?purpose=${p.demo}" ${NT}>Talk to us / Request Demo</a></div></div></div>`;
  m.classList.add('active');
  document.body.style.overflow='hidden';
};
window.closeProductModal=function(){const m=$('#prod-modal');if(m){m.classList.remove('active');document.body.style.overflow=''}};
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeProductModal()});
$$('[data-list]').forEach(e=>{const k=e.dataset.list,n=+e.dataset.max||99,items=V[k].slice(0,n);e.innerHTML=items.map(card).join('');
 const f=$('#'+e.dataset.filter);if(f){const tags=['All',...new Set(items.map(x=>x.tag))];f.innerHTML=tags.map((t,i)=>`<button aria-pressed="${!i}">${t}</button>`).join('');
  $$('button',f).forEach(b=>b.onclick=()=>{$$('button',f).forEach(q=>q.setAttribute('aria-pressed',q===b));e.innerHTML=items.filter(x=>b.textContent==='All'||x.tag===b.textContent).map(card).join('');reveal()})}});
$$('[data-rail]').forEach(w=>{const r=$('.rail',w);$('.pv',w).onclick=()=>r.scrollBy({left:-360,behavior:'smooth'});$('.nx',w).onclick=()=>r.scrollBy({left:360,behavior:'smooth'})});

/* banner slider (auto, arrows, dots, swipe) */
(function(){const el=$('[data-slider]');if(!el)return;
 const CL=[['#1d6b0a','#06210a'],['#0076A8','#012a3d'],['#3a3d40','#000']],HD=['One system for the whole school.','The whole trip, booked in one place.','Stay close to the people you serve.'];
 const sl=[...V.products.map((p,i)=>({c:CL[i%3],k:p.sector,h:HD[i]||p.name,t:p.desc,p1:[p.cta,p.url,1],p2:['Talk to us','contact.html?purpose='+p.demo,1],art:p.name})),
 {c:['#0076A8','#0e2230'],k:'Independent non-profit',h:'Nayibeej works for every social category.',t:'Supported by VOTESS, governed and funded independently.',p1:['Learn about Nayibeej','#responsibility',0],art:'Nayibeej'},
 {c:['#000','#26890D'],k:'Work with VOTESS',h:'Have a project? Send us your RFP.',t:'Tell us what you need and we will respond with a clear plan.',p1:['Submit RFP','submit-rfp.html',1],p2:['Talk to us','contact.html',1],art:'RFP'}];
 const b=(x,p)=>p?`<a class="btn${p===x.p1?'':' o'}" href="${p[1]}" ${p[2]?NT:''}>${p[0]}</a>`:'';
 $('.track',el).innerHTML=sl.map(x=>`<div class="slide" style="--a:${x.c[0]};--b:${x.c[1]}"><div class="wrap"><div><small>${x.k.toUpperCase()}</small><h2>${x.h}</h2><p>${x.t}</p><div class="acts">${b(x,x.p1)}${b(x,x.p2)}</div></div><div class="art" aria-hidden="true"><span>${x.art}</span></div></div></div>`).join('');
 const tr=$('.track',el),n=sl.length,dots=$('.dots',el);let i=0,t,x0=null;
 for(let k=0;k<n;k++){const d=document.createElement('button');d.setAttribute('aria-label','Go to slide '+(k+1));d.onclick=()=>go(k);dots.append(d)}
 function go(k){i=(k+n)%n;tr.style.transform=`translateX(-${i*100}%)`;[...dots.children].forEach((d,j)=>d.setAttribute('aria-current',j===i))}
 const play=()=>{clearInterval(t);if(!RM)t=setInterval(()=>go(i+1),6500)},stop=()=>clearInterval(t);
 $('.prev',el).onclick=()=>go(i-1);$('.next',el).onclick=()=>go(i+1);
 el.onmouseenter=stop;el.onmouseleave=play;el.onfocusin=stop;el.onfocusout=play;
 el.onpointerdown=e=>x0=e.clientX;el.onpointerup=e=>{if(x0!==null&&Math.abs(e.clientX-x0)>50)go(i+(e.clientX<x0?1:-1));x0=null};
 go(0);play()})();

/* Nayibeej areas (home) */
(function(){const a=$('#ar');if(!a)return;const AR={Education:'School software and portals could help the schools and learners Nayibeej works with.',Healthcare:'Simple records and reminder messaging could support health camps and partners.','Women & child welfare':'Secure registration, case records and messaging could help programmes run safely and privately.','Livelihoods & skills':'Sign-ups, attendance tracking and learning platforms could support skill programmes.','Farmer support':'Messaging and information tools could carry advice and updates to farmers.',Environment:'Volunteer sign-ups, event pages and reporting dashboards could support campaigns.',Relief:'Fast messaging, donation pages and volunteer coordination could help in emergencies.'};
 Object.keys(AR).forEach(k=>{const b=document.createElement('button');b.textContent=k;b.setAttribute('aria-pressed','false');b.onclick=()=>{$$('#ar button').forEach(q=>q.setAttribute('aria-pressed',q===b));$('#arx').textContent='Possible VOTESS support: '+AR[k]};a.append(b)})})();

/* lead form: contact page and RFP page post to api/lead.php */
(function(){const f=$('#lf');if(!f)return;
 const P={erp_demo:'Nexus ERP: book a demo',travel:'GoVacayTrip: travel enquiry or partnership',chat:'Ciao Chat: communication / engagement tools',digital:'Votess Digital: website, app or digital project',cloud:'Votess Cloud / Connect: hosting and integrations',labs:'Votess Labs: product or AI idea',partnership:'Business partnership or investment',nayibeej:'Nayibeej: volunteer, partner or support',careers:'Careers at VOTESS',media:'Media or general enquiry',support:'Support for an existing product'};
 const SUB={erp_demo:['School','College / university','Group of institutions','Coaching / training centre'],travel:['Corporate travel','Holiday packages','Agent / partner','Other'],chat:['Messaging','Customer engagement','Integration','Other'],digital:['Website','Mobile app','Cloud / automation','Analytics'],cloud:['Hosting','APIs / integrations','Payments','Security'],labs:['SaaS idea','AI project','Research partnership','Other'],partnership:['Reseller / channel','Investment','Joint venture','Other'],nayibeej:['Volunteer','Partner with Nayibeej','Support or sponsor','I need help from Nayibeej'],careers:['Engineering','Design','Sales and partnerships','Operations'],media:['Press','Speaking','General question','Other'],support:['Nexus ERP','GoVacayTrip','Ciao Chat','Other']};
 const HINT={nayibeej:'Nayibeej is an independent non-profit supported by VOTESS. We will pass your message to the right person. Donations are not collected through this form.',careers:'Tell us about your experience in the message box and add a link to your portfolio or profile.',support:'Include the product name and any reference numbers so we can help faster.'};
 const pg=$('#pg');if(pg){pg.innerHTML=Object.entries(P).map(([k,v])=>`<label><input type="radio" name="purpose" value="${k}" required><span>${v}</span></label>`).join('');
  const q=new URLSearchParams(location.search).get('purpose'),r=q&&$(`input[value="${q}"]`,pg);if(r)r.checked=true;
  const upd=()=>{const v=(f.purpose&&f.purpose.value)||'',s=$('#sub');s.innerHTML='<option value="">Select (optional)</option>'+(SUB[v]||[]).map(x=>`<option>${x}</option>`).join('');$('#hint').hidden=!HINT[v];$('#hint').textContent=HINT[v]||''};
  pg.onchange=upd;upd()}
 const u=new URLSearchParams(location.search);['utm_source','utm_medium','utm_campaign'].forEach(k=>{if(f[k])f[k].value=u.get(k)||''});
 f.onsubmit=async e=>{e.preventDefault();$$('.er',f).forEach(x=>x.textContent='');const b=$('button[type=submit]',f),old=b.textContent;b.disabled=true;b.textContent='Sending...';
  const d={};new FormData(f).forEach((v,k)=>d[k]=v);['consent_privacy','consent_marketing'].forEach(k=>d[k]=f[k]&&f[k].checked?1:0);if(f.dataset.purpose)d.purpose=f.dataset.purpose;d.source_url=location.href;
  const fb=why=>{const fe=$('#fe');fe.textContent=why+' ';const a=document.createElement('a');a.href='mailto:'+V.email+'?subject='+encodeURIComponent('Enquiry from '+(d.name||''))+'&body='+encodeURIComponent((d.message||'')+'\n\n'+(d.name||'')+' '+(d.email||'')+' '+(d.phone||''));a.textContent='Send this by email instead';a.style.textDecoration='underline';fe.append(a)};
  try{const r=await fetch('api/lead.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});let j=null;try{j=await r.json()}catch(_){}
   if(!j)fb('The form service did not respond correctly (HTTP '+r.status+').');
   else if(j.ok){f.outerHTML=`<div class="ok" role="status"><h2>Thank you. We have your message.</h2><p style="margin:.8rem 0">Your reference is <b>${j.ref}</b>. A confirmation has been emailed to you and our team will reply shortly.</p><a class="btn" href="index.html">Back to home</a></div>`;scrollTo({top:0,behavior:'smooth'});return}
   else{Object.entries(j.errors||{}).forEach(([k,m])=>{const el=$(`[data-er="${k}"]`,f);if(el)el.textContent=m});
    if(j.error)fb(j.error);const first=$('.er:not(:empty)',f);if(first)first.scrollIntoView({block:'center',behavior:'smooth'})}}
  catch(x){fb('Could not reach the server. Check your connection and that you are on the live website address.')}
  b.disabled=false;b.textContent=old}})();
reveal();

/* ===== Image slider: auto-slide, arrows, dots, swipe, pause-on-hover ===== */
(function(){
  $$('.img-slider').forEach(sl=>{
    const track=$('.img-track',sl),slides=$$('.img-slide',sl),n=slides.length,dotsC=$('.img-dots',sl);
    if(!n)return;
    let i=0,timer,x0=null;
    const autoMs=+(sl.dataset.auto||5000);
    // create dots
    for(let k=0;k<n;k++){const d=document.createElement('button');d.setAttribute('aria-label','Go to slide '+(k+1));d.onclick=()=>go(k);dotsC.append(d)}
    function go(k){i=(k%n+n)%n;track.style.transform=`translateX(-${i*100}%)`;[...dotsC.children].forEach((d,j)=>{d.classList.toggle('active',j===i)})}
    function play(){clearInterval(timer);if(!RM)timer=setInterval(()=>go(i+1),autoMs)}
    function stop(){clearInterval(timer)}
    // arrows
    const pBtn=$('.img-nav.prev',sl),nBtn=$('.img-nav.next',sl);
    if(pBtn)pBtn.onclick=()=>{go(i-1);play()};
    if(nBtn)nBtn.onclick=()=>{go(i+1);play()};
    // hover pause
    sl.onmouseenter=stop;sl.onmouseleave=play;sl.onfocusin=stop;sl.onfocusout=play;
    // swipe
    sl.onpointerdown=e=>x0=e.clientX;
    sl.onpointerup=e=>{if(x0!==null&&Math.abs(e.clientX-x0)>40){go(i+(e.clientX<x0?1:-1));play()}x0=null};
    go(0);play();
  });
})();

/* ===== VOTESS Chatbot ===== */
(function(){
  const FLOWS={
    start:{msg:"👋 Hi! I'm VOTESS Assistant. How can I help you today?",opts:[
      {t:'Products & Services',n:'products'},{t:'Nayibeej Non-profit',n:'nayibeej'},
      {t:'Work with us / RFP',n:'work'},{t:'Careers',n:'careers'},{t:'Talk to a human',n:'collect'}]},
    products:{msg:'Which product are you interested in?',opts:[
      {t:'Nexus ERP (Education)',n:'nexus'},{t:'GoVacayTrip (Travel)',n:'govacay'},
      {t:'Ciao Chat (Communication)',n:'ciao'},{t:'Non-profit ERP / CRM',n:'ngoerp'},{t:'↩ Back',n:'start'}]},
    nexus:{msg:'Nexus ERP is our school management platform covering admissions, finance, HR, LMS, parent portals and transport — all in one system. Would you like a demo?',opts:[{t:'Book a demo',n:'collect'},{t:'↩ Back',n:'products'}]},
    govacay:{msg:'GoVacayTrip lets travellers book flights, hotels, holidays and buses in one place under one brand. Want to know more or partner?',opts:[{t:'Partner inquiry',n:'collect'},{t:'↩ Back',n:'products'}]},
    ciao:{msg:'Ciao Chat provides messaging and customer-engagement tools for organisations staying close to the people they serve. Want a walkthrough?',opts:[{t:'Get in touch',n:'collect'},{t:'↩ Back',n:'products'}]},
    ngoerp:{msg:'Our Non-profit ERP/CRM is a comprehensive A-to-Z management system for NGOs — donor management, project tracking, beneficiary records, volunteer coordination, task management, HR, accounting, website and reporting. Interested?',opts:[{t:'Request a demo',n:'collect'},{t:'↩ Back',n:'products'}]},
    nayibeej:{msg:'Nayibeej is an independent non-profit supported by VOTESS. It works across education, healthcare, livelihoods, environment and more. VOTESS does not own or control it.',opts:[
      {t:'Volunteer',n:'collect'},{t:'Partner or support',n:'collect'},{t:'↩ Back',n:'start'}]},
    work:{msg:'Great! Tell us about your project and we will get back to you with a clear plan. Which best describes your need?',opts:[
      {t:'Digital transformation',n:'collect'},{t:'Enterprise software',n:'collect'},{t:'Cloud & integration',n:'collect'},{t:'Submit formal RFP',n:'rfp'}]},
    rfp:{msg:'Perfect. You can submit your RFP directly on our website.',opts:[{t:'Open RFP page',n:'_rfp'},{t:'↩ Back',n:'work'}]},
    careers:{msg:'We are growing! Which area interests you?',opts:[
      {t:'Engineering & Tech',n:'collect'},{t:'Design',n:'collect'},{t:'Sales & Partnerships',n:'collect'},{t:'Operations',n:'collect'}]},
    collect:{msg:'Great! To connect you with the right person, could I get a few quick details?',opts:[],form:true}
  };
  let phase='start',collected={};

  const btn=document.createElement('button');btn.id='chatbot-btn';btn.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="26" height="26"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg><span class="cb-dot"></span>';btn.setAttribute('aria-label','Open chat assistant');
  const box=document.createElement('div');box.id='chatbot';
  box.innerHTML=`<div class="cb-head"><div><h4>VOTESS Assistant</h4><span>Typically replies instantly</span></div><button class="cb-close" aria-label="Close chat">&#x2715;</button></div><div class="cb-body" id="cb-body"></div><div class="cb-opts" id="cb-opts"></div><div class="cb-inp" id="cb-inp" style="display:none"><input type="text" id="cb-text" placeholder="Type your answer..."><button id="cb-send">Send</button></div>`;
  document.body.append(btn,box);

  const body=$('#cb-body'),opts=$('#cb-opts'),inpWrap=$('#cb-inp'),txt=$('#cb-text'),send=$('#cb-send');
  btn.onclick=()=>{box.classList.toggle('open');if(box.classList.contains('open')&&!body.children.length)render('start')};
  box.querySelector('.cb-close').onclick=()=>box.classList.remove('open');

  function addMsg(text,who){const m=document.createElement('div');m.className='cb-msg '+who;m.textContent=text;body.append(m);body.scrollTop=body.scrollHeight}
  function render(key){
    phase=key;const f=FLOWS[key];if(!f)return;
    if(key==='_rfp'){window.open('submit-rfp.html','_blank');return}
    setTimeout(()=>{
      addMsg(f.msg,'bot');
      opts.innerHTML='';
      inpWrap.style.display='none';
      if(f.form){showForm();return}
      f.opts.forEach(o=>{const b=document.createElement('button');b.textContent=o.t;b.onclick=()=>{addMsg(o.t,'usr');render(o.n)};opts.append(b)})
    },320)
  }
  function showForm(){
    const step=Object.keys(collected).length;
    if(step===0){inpWrap.style.display='flex';txt.placeholder='Your name...';txt.value='';txt.focus()
    }else if(step===1){inpWrap.style.display='flex';txt.placeholder='Your email address...';txt.value='';txt.focus()
    }else if(step===2){inpWrap.style.display='flex';txt.placeholder='Your phone / WhatsApp (optional)...';txt.value='';txt.focus()
    }else{inpWrap.style.display='none';addMsg('Thank you, '+collected.name+'! Our team will reach out to you at '+collected.email+' shortly. You can also email us at info@votess.in.','bot');setTimeout(()=>{opts.innerHTML='';const b=document.createElement('button');b.textContent='↩ Start over';b.onclick=()=>{collected={};render('start')};opts.append(b)},400)}
  }
  send.onclick=handleInput;
  txt.onkeydown=e=>{if(e.key==='Enter')handleInput()};
  function handleInput(){
    const v=txt.value.trim();if(!v)return;
    addMsg(v,'usr');
    const step=Object.keys(collected).length;
    if(step===0)collected.name=v;
    else if(step===1){if(!/\S+@\S+/.test(v)){addMsg('Please enter a valid email address.','bot');inpWrap.style.display='flex';txt.value='';txt.focus();return}collected.email=v}
    else if(step===2)collected.phone=v;
    showForm();
  }
})();
