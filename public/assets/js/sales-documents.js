(() => {
    const clear = document.querySelector('[data-clear-sales-ref]');
    if (clear?.dataset.clearSalesRef) {
        const savedReference=clear.dataset.clearSalesRef; localStorage.removeItem(`formmix-sales-draft-${savedReference}`);
        ['quote','order'].forEach(type=>{const key=`formmix-sales-active-${type}`;if(localStorage.getItem(key)===savedReference)localStorage.removeItem(key);});
    }
    const form = document.querySelector('[data-sales-document]');
    if (!form) return;
    const catalog = JSON.parse(form.querySelector('[data-catalog-json]')?.value || '[]');
    const initial = JSON.parse(form.querySelector('[data-initial-items]')?.value || '[]');
    const customer = form.querySelector('[data-sales-customer]');
    const picker = form.querySelector('[data-product-picker]');
    const search = form.querySelector('[data-product-search]');
    const linesNode = form.querySelector('[data-sales-lines]');
    const reference = form.querySelector('[data-client-reference]');
    const stateNode = form.querySelector('[data-draft-state]');
    const money = value => new Intl.NumberFormat('tr-TR', {style: 'currency', currency: 'TRY'}).format(value || 0);
    const uuid = () => crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random().toString(16).slice(2)}-${Math.random().toString(16).slice(2)}`;
    const documentType=form.querySelector('input[name="document_type"]')?.value||'order'; const activeKey=`formmix-sales-active-${documentType}`;
    if (!reference.value) reference.value = localStorage.getItem(activeKey) || uuid();
    localStorage.setItem(activeKey,reference.value);
    const storageKey = () => `formmix-sales-draft-${reference.value}`;
    let lines = [];
    const setState = text => { stateNode.textContent = text; };
    const serialize = () => ({document_type:documentType,customer_id: customer.value, delivery_address: form.delivery_address.value, requested_delivery_date: form.requested_delivery_date.value, notes: form.notes.value, sales_employee_id: form.sales_employee_id?.value || '', lines});
    const saveLocal = () => { localStorage.setItem(storageKey(), JSON.stringify(serialize())); setState(navigator.onLine ? 'Cihazda kaydedildi' : 'Bağlantı yok · cihazda kaydedildi'); };
    const totals = () => {
        let subtotal=0, discount=0, tax=0, grand=0;
        lines.forEach(line => { const gross=line.quantity*line.unit_price; const cut=gross*line.discount_percent/100; const net=gross-cut; const vat=net*line.tax_rate/100; subtotal+=gross; discount+=cut; tax+=vat; grand+=net+vat; });
        form.querySelector('[data-summary-subtotal]').textContent=money(subtotal); form.querySelector('[data-summary-discount]').textContent=money(discount); form.querySelector('[data-summary-tax]').textContent=money(tax); form.querySelector('[data-summary-grand]').textContent=money(grand);
    };
    const render = () => {
        linesNode.innerHTML='';
        if (!lines.length) { const empty=document.createElement('p'); empty.className='muted'; empty.textContent='Henüz ürün eklenmedi.'; linesNode.append(empty); totals(); return; }
        lines.forEach((line,index) => {
            const entry=catalog.find(item => item.product_id===Number(line.product_id)&&item.variant_id===Number(line.product_variant_id));
            const card=document.createElement('article'); card.className='sales-line';
            const visual=document.createElement(entry?.image?'img':'span'); if(entry?.image){visual.src=entry.image;visual.alt='';}else visual.textContent=(entry?.name||line.product_name_snapshot||'Ü').slice(0,1);
            const copy=document.createElement('div'); copy.className='sales-line__copy'; const strong=document.createElement('strong');strong.textContent=entry?.name||line.product_name_snapshot||'Ürün';const small=document.createElement('small');small.textContent=[entry?.variant||line.variant_snapshot,entry?.sku||line.product_code_snapshot].filter(Boolean).join(' · ');copy.append(strong,small);
            const quantity=document.createElement('label');quantity.innerHTML='<span>Miktar</span>';const quantityInput=document.createElement('input');quantityInput.type='number';quantityInput.min='0.001';quantityInput.step='0.001';quantityInput.value=String(line.quantity);quantity.append(quantityInput);
            const discount=document.createElement('label');discount.innerHTML='<span>İndirim %</span>';const discountInput=document.createElement('input');discountInput.type='number';discountInput.min='0';discountInput.max='100';discountInput.step='0.01';discountInput.value=String(line.discount_percent);discount.append(discountInput);
            const price=document.createElement('div');price.className='sales-line__price';price.innerHTML='<span>Birim fiyat</span>';const priceStrong=document.createElement('strong');priceStrong.textContent=money(line.unit_price);const totalSmall=document.createElement('small');price.append(priceStrong,totalSmall);
            const remove=document.createElement('button');remove.type='button';remove.className='icon-button sales-line__remove';remove.setAttribute('aria-label','Ürünü kaldır');remove.textContent='×';
            const update=()=>{line.quantity=Math.max(0,Number(quantityInput.value)||0);line.discount_percent=Math.min(100,Math.max(0,Number(discountInput.value)||0));const gross=line.quantity*line.unit_price;const net=gross-(gross*line.discount_percent/100);totalSmall.textContent=`Vergi dahil ${money(net*(1+line.tax_rate/100))}`;totals();saveLocal();};quantityInput.addEventListener('input',update);discountInput.addEventListener('input',update);remove.addEventListener('click',()=>{lines.splice(index,1);render();saveLocal();});
            card.append(visual,copy,quantity,discount,price,remove);linesNode.append(card);update();
        }); totals();
    };
    const resolvePrice = async entry => {
        if (!customer.value) throw new Error('Önce müşteri seçin.');
        const url=new URL(form.dataset.priceUrl,location.origin);url.searchParams.set('musteri',customer.value);url.searchParams.set('urun',entry.product_id);url.searchParams.set('varyant',entry.variant_id);
        const response=await fetch(url,{headers:{Accept:'application/json'}});const data=await response.json();if(!response.ok||!data.ok)throw new Error(data.message||'Fiyat alınamadı.');return data;
    };
    form.querySelector('[data-add-line]')?.addEventListener('click',async()=>{const [productId,variantId]=(picker.value||'').split(':').map(Number);const entry=catalog.find(item=>item.product_id===productId&&item.variant_id===variantId);if(!entry)return;setState('Fiyat alınıyor…');try{const price=await resolvePrice(entry);lines.push({product_id:entry.product_id,product_variant_id:entry.variant_id,quantity:1,discount_percent:0,unit_price:Number(price.unit_price),tax_rate:Number(price.tax_rate)});render();saveLocal();picker.value='';}catch(error){setState(error.message);}});
    search?.addEventListener('input',()=>{const needle=search.value.toLocaleLowerCase('tr-TR');[...picker.options].forEach((option,index)=>{if(index)option.hidden=needle!==''&&!option.textContent.toLocaleLowerCase('tr-TR').includes(needle);});});
    customer?.addEventListener('change',async()=>{for(const line of lines){const entry=catalog.find(item=>item.product_id===Number(line.product_id)&&item.variant_id===Number(line.product_variant_id));if(entry){try{const price=await resolvePrice(entry);line.unit_price=Number(price.unit_price);line.tax_rate=Number(price.tax_rate);}catch{line.unit_price=0;}}}render();saveLocal();});
    form.querySelectorAll('input,select,textarea').forEach(input=>{if(!input.matches('[data-product-picker],[data-product-search]'))input.addEventListener('input',saveLocal);});
    form.querySelectorAll('[data-save-intent]').forEach(button=>button.addEventListener('click',()=>{form.querySelector('[data-intent]').value=button.dataset.saveIntent;}));
    form.addEventListener('submit',event=>{form.querySelector('[data-items-json]').value=JSON.stringify(lines.map(({product_id,product_variant_id,quantity,discount_percent})=>({product_id,product_variant_id,quantity,discount_percent})));saveLocal();if(!navigator.onLine){event.preventDefault();setState('Bağlantı yok · taslak cihazda korundu');return;}setState('Gönderiliyor…');form.querySelectorAll('button[type=submit]').forEach(button=>button.disabled=true);});
    window.addEventListener('offline',()=>setState('Bağlantı yok · taslak cihazda korunuyor'));window.addEventListener('online',()=>setState('Bağlantı geri geldi · göndermeye hazır'));
    const saved=localStorage.getItem(storageKey());
    if(initial.length){lines=initial.map(item=>({product_id:Number(item.product_id),product_variant_id:Number(item.product_variant_id),quantity:Number(item.quantity),discount_percent:Number(item.discount_percent),unit_price:Number(item.unit_price),tax_rate:Number(item.tax_rate),product_name_snapshot:item.product_name_snapshot,variant_snapshot:item.variant_snapshot,product_code_snapshot:item.product_code_snapshot}));}
    else if(saved){try{const draft=JSON.parse(saved);customer.value=draft.customer_id||customer.value;form.delivery_address.value=draft.delivery_address||'';form.requested_delivery_date.value=draft.requested_delivery_date||'';form.notes.value=draft.notes||'';if(form.sales_employee_id)form.sales_employee_id.value=draft.sales_employee_id||'';lines=Array.isArray(draft.lines)?draft.lines:[];setState('Cihazdaki taslak geri yüklendi');}catch{localStorage.removeItem(storageKey());}}
    render();
})();
