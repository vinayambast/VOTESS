/* Payment page: Razorpay, Cashfree, PayU, Easebuzz and UPI (QR + deep link). Server: api/pay_*.php */
(function(){const f=$('#pf');if(!f)return;
 const NAMES={razorpay:'Razorpay (cards, UPI, netbanking, wallets)',cashfree:'Cashfree (cards, UPI, netbanking)',payu:'PayU (cards, UPI, netbanking)',easebuzz:'Easebuzz (cards, UPI, netbanking)',upi:'UPI QR code (scan or tap, then confirm UTR)'};
 const q=new URLSearchParams(location.search),st=q.get('status');
 if(st){const ok=st==='paid';$('#ps').innerHTML=`<div class="ok${ok?'':' bad'}" role="status"><h2>${ok?'Payment received. Thank you.':'Payment not completed.'}</h2><p style="margin-top:.6rem">${ok?'A receipt has been emailed to you. ':'No money was taken, or it will be refunded by your bank. You can try again below. '}Reference: <b>${(q.get('ref')||'').replace(/[^A-Za-z0-9-]/g,'')}</b></p></div>`}
 const load=(src)=>new Promise((res,rej)=>{if(document.querySelector(`script[src="${src}"]`))return res();const s=document.createElement('script');s.src=src;s.onload=res;s.onerror=rej;document.head.append(s)});
 const post=(u,d)=>fetch(u,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)}).then(r=>r.json());
 fetch('api/pay_create.php').then(r=>r.json()).then(c=>{const g=c.gateways||[];
  $('#gw').innerHTML=g.length?g.map((k,i)=>`<label><input type="radio" name="gateway" value="${k}" ${i?'':'checked'}><span>${NAMES[k]}</span></label>`).join(''):'<p class="er">No payment method is configured yet.</p>';
  if(c.live===false)$('#tm').hidden=false}).catch(()=>{$('#gw').innerHTML='<p class="er">Could not load payment methods.</p>'});
 f.onsubmit=async e=>{e.preventDefault();$$('.er',f).forEach(x=>x.textContent='');const b=$('button[type=submit]',f);b.disabled=true;
  const d={};new FormData(f).forEach((v,k)=>d[k]=v);
  try{const j=await post('api/pay_create.php',d);
   if(!j.ok){Object.entries(j.errors||{}).forEach(([k,m])=>{const el=$(`[data-er="${k}"]`,f);if(el)el.textContent=m});$('#fe').textContent=j.error||'';b.disabled=false;return}
   if(j.gateway==='razorpay'){await load('https://checkout.razorpay.com/v1/checkout.js');
    new Razorpay({key:j.key,order_id:j.order_id,amount:j.amount,currency:'INR',name:j.name,description:j.description,prefill:j.prefill,theme:{color:'#86BC25'},
     handler:async r=>{const v=await post('api/pay_verify.php',{action:'razorpay',...r});location.href='pay.html?status='+(v.ok?'paid':'failed')+'&ref='+j.ref},
     modal:{ondismiss:()=>{b.disabled=false}}}).open()}
   else if(j.gateway==='cashfree'){await load('https://sdk.cashfree.com/js/v3/cashfree.js');Cashfree({mode:j.mode}).checkout({paymentSessionId:j.session_id,redirectTarget:'_self'})}
   else if(j.gateway==='payu'){const p=document.createElement('form');p.method='POST';p.action=j.action;Object.entries(j.fields).forEach(([k,v])=>{const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;p.append(i)});document.body.append(p);p.submit()}
   else if(j.gateway==='easebuzz')location.href=j.redirect;
   else if(j.gateway==='upi'){f.hidden=true;$('#upi').hidden=false;$('#upi-amt').textContent='INR '+j.amount;$('#upi-vpa').textContent=j.vpa;
    $('#upi-app').href=j.link;const box=$('#qr');box.innerHTML='';
    if(j.qr_image)box.innerHTML=`<img src="${j.qr_image}" alt="UPI QR code" width="240">`;
    else{await load('https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js');new QRCode(box,{text:j.link,width:220,height:220})}
    $('#utr-btn').onclick=async()=>{const v=await post('api/pay_verify.php',{action:'upi_utr',ref:j.ref,utr:$('#utr').value});
     $('#utr-er').textContent=v.ok?'':(v.error||'Could not save.');if(v.ok)$('#upi').innerHTML=`<div class="ok" role="status"><h2>Thank you. We will confirm your payment.</h2><p style="margin-top:.6rem">Reference <b>${j.ref}</b>. We will email you a receipt once we verify the UPI credit.</p></div>`}}
  }catch(x){$('#fe').textContent='Something went wrong. Please try again.';b.disabled=false}}})();
