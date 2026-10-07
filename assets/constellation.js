/* Hero constellation (home page). Needs site.js ($ and RM helpers). */
(function(){
/* constellation */
const N=[
{k:'core',n:'VOTESS',g:'Parent company',d:'Technology and enterprise systems. The backbone every brand runs on.',R:0,s:0,p:0,r:17,c:'#86BC25'},
{k:'nx',n:'Nexus ERP',g:'A VOTESS Product',d:'School management software for administrators, teachers, students and parents.',R:.40,s:.32,p:.4,r:8,c:'#FFFFFF'},
{k:'gv',n:'GoVacayTrip',g:'Powered by VOTESS',d:'Flights, hotels, holidays and bus booking under its own travel identity.',R:.40,s:.32,p:2.5,r:8,c:'#FFFFFF'},
{k:'cc',n:'Ciao Chat',g:'A VOTESS Product / Service',d:'Communication and customer-engagement tools for organisations.',R:.40,s:.32,p:4.6,r:8,c:'#FFFFFF'},
{k:'dg',n:'Digital',g:'VOTESS business unit',d:'Web, apps, ERP, cloud and automation services.',R:.66,s:-.2,p:1.2,r:5,c:'#BBBCBC'},
{k:'lb',n:'Labs',g:'VOTESS business unit',d:'New SaaS products, AI and experiments.',R:.66,s:-.2,p:3.3,r:5,c:'#BBBCBC'},
{k:'cl',n:'Cloud / Connect',g:'Shared infrastructure',d:'Hosting, APIs, identity, payments and notifications.',R:.66,s:-.2,p:5.4,r:5,c:'#BBBCBC'},
{k:'ny',n:'Nayibeej',g:'Independent non-profit',d:'Supports people across all social categories. Supported by VOTESS, never owned by it.',R:.93,s:.07,p:5.9,r:10,c:'#00A3E0',np:1}];
const cv=$('#cv'),x=cv.getContext('2d'),rd=$('#read'),pk=$('#pick');let rip=[],W,H,dpr,t=0,mx=0,my=0,tx=0,ty=0,act='core',pos={},vis=true;
function size(){const r=cv.getBoundingClientRect();dpr=Math.min(devicePixelRatio||1,2);W=r.width;H=r.height;cv.width=W*dpr;cv.height=H*dpr;x.setTransform(dpr,0,0,dpr,0,0)}
function set(k){act=k;const o=N.find(n=>n.k===k);rd.className='read'+(o.np?' np':'');rd.innerHTML=`<span class="k">${o.g}</span><h3>${o.n}</h3><p>${o.d}</p>`;
pk.querySelectorAll('button').forEach(b=>b.setAttribute('aria-pressed',b.dataset.k===k));if(RM)draw()}
N.forEach(o=>{const b=document.createElement('button');b.textContent=o.n;b.dataset.k=o.k;b.className=o.np?'np':'';b.setAttribute('aria-pressed','false');b.onclick=()=>set(o.k);pk.append(b)});
function draw(){x.clearRect(0,0,W,H);const cx=W/2,cy=H/2,M=W/2-34;mx+=(tx-mx)*.06;my+=(ty-my)*.06;
[.40,.66,.93].forEach(R=>{x.beginPath();x.ellipse(cx+mx*4,cy+my*4,R*M,R*M*.72,0,0,7);x.strokeStyle=R>.9?'#00A3E044':'#ffffff14';x.setLineDash(R>.9?[3,7]:[]);x.lineWidth=1;x.stroke()});x.setLineDash([]);
N.forEach((o,i)=>{const a=o.p+t*o.s,d=o.R?(.5+o.R*.7):0;o.x=cx+Math.cos(a)*o.R*M+mx*10*d;o.y=cy+Math.sin(a)*o.R*M*.72+my*10*d;pos[o.k]=o});
const c0=pos.core;rip=rip.filter(q=>t-q.t<1.5);rip.forEach(q=>{const a=t-q.t;x.beginPath();x.arc(q.x,q.y,a*170,0,7);x.strokeStyle=`rgba(134,188,37,${.5*(1-a/1.5)})`;x.lineWidth=1.5;x.stroke()});
N.forEach(o=>{if(o===c0)return;x.beginPath();x.moveTo(c0.x,c0.y);x.lineTo(o.x,o.y);x.lineWidth=o.k===act?1.5:1;
if(o.np){x.setLineDash([5,8]);x.lineDashOffset=-t*24;x.strokeStyle=o.k===act?'#00A3E0':'#00A3E055'}else{x.setLineDash([]);x.strokeStyle=o.k===act?'#86BC25':'#ffffff1c'}x.stroke();x.setLineDash([])});
N.forEach(o=>{const on=o.k===act,r=o.r*(on?1.35:1),g=x.createRadialGradient(o.x,o.y,0,o.x,o.y,r*4);g.addColorStop(0,o.c+(on?'88':'44'));g.addColorStop(1,o.c+'00');
x.fillStyle=g;x.beginPath();x.arc(o.x,o.y,r*4,0,7);x.fill();x.fillStyle=o.c;x.beginPath();x.arc(o.x,o.y,r,0,7);x.fill();
if(on){x.strokeStyle=o.c;x.lineWidth=1.5;x.beginPath();x.arc(o.x,o.y,r+7+Math.sin(t*3)*1.5,0,7);x.stroke()}
x.font='600 12px Hanken Grotesk,system-ui,sans-serif';x.fillStyle=on?'#fff':'#ffffffaa';x.textAlign='center';x.fillText(o.n,o.x,o.y+r+(o.k==='core'?22:18))})}
let last=0;function loop(ts){if(vis&&!RM){t+=Math.min(.05,(ts-last)/1000);draw()}last=ts;requestAnimationFrame(loop)}
function hit(e){const r=cv.getBoundingClientRect(),px=e.clientX-r.left,py=e.clientY-r.top;tx=(px/r.width-.5)*2;ty=(py/r.height-.5)*2;
let b=null,bd=30;for(const o of N){const d=Math.hypot(o.x-px,o.y-py);if(d<bd){bd=d;b=o}}return b}
cv.addEventListener('pointermove',e=>{const b=hit(e);if(b&&b.k!==act)set(b.k);if(RM)draw()});
cv.addEventListener('pointerdown',e=>{const b=hit(e),r=cv.getBoundingClientRect();if(!RM)rip.push({x:e.clientX-r.left,y:e.clientY-r.top,t});if(b)set(b.k)});
cv.addEventListener('pointerleave',()=>{tx=ty=0});
new IntersectionObserver(([e])=>vis=e.isIntersecting).observe(cv);
addEventListener('resize',()=>{size();if(RM)draw()});size();draw();set('core');requestAnimationFrame(loop);new ResizeObserver(()=>{size();draw()}).observe(cv);


})();
