import { useQuery } from '@tanstack/react-query';
import { useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { cachedApi as api } from '@/cachedApi';
import { Card, Empty, Field, Header, Loading, Screen } from '@/components';
import { colors } from '@/theme';
import type { Product } from '@/types';
type Response={data:Product[];meta:{total:number}};
export default function Products(){const[q,setQ]=useState('');const query=useQuery({queryKey:['products',q],queryFn:()=>api<Response>(`/products?q=${encodeURIComponent(q)}&per_page=50`)});return <Screen><Header title="Urun katalogu" subtitle={`${query.data?.meta.total??0} urun`}/><Field label="Ara" placeholder="Urun adi veya kod" value={q} onChangeText={setQ}/>{query.isLoading?<Loading/>:query.data?.data.length?query.data.data.map(p=><Card key={p.id}><Text style={styles.name}>{p.name}</Text><Text style={styles.meta}>{p.product_code} · {p.variants.length} varyant</Text><Text style={styles.price}>{new Intl.NumberFormat('tr-TR',{style:'currency',currency:'TRY'}).format(Number(p.list_price))}</Text></Card>):<Empty text="Urun bulunamadi."/>}</Screen>}
const styles=StyleSheet.create({name:{fontSize:17,fontWeight:'700',color:colors.navy},meta:{color:colors.muted},price:{fontSize:18,fontWeight:'800',color:colors.orange}});
