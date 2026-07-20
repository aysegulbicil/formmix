import { useQuery } from '@tanstack/react-query';
import { router } from 'expo-router';
import { useState } from 'react';
import { Pressable, StyleSheet, Text } from 'react-native';
import { cachedApi as api } from '@/cachedApi';
import { Button, Card, Empty, Field, Header, Loading, Screen } from '@/components';
import { colors } from '@/theme';
import type { Customer } from '@/types';
type Response={data:Customer[];meta:{total:number}};
export default function Customers(){const[q,setQ]=useState('');const query=useQuery({queryKey:['customers',q],queryFn:()=>api<Response>(`/customers?q=${encodeURIComponent(q)}`)});return <Screen><Header title="Musteriler" subtitle={`${query.data?.meta.total??0} kayit`} action={<Button title="+ Ekle" onPress={()=>router.push('/customer/new')}/>}/><Field label="Ara" placeholder="Firma, kod veya telefon" value={q} onChangeText={setQ}/>{query.isLoading?<Loading/>:query.data?.data.length?query.data.data.map(c=><Pressable key={c.id} onPress={()=>router.push(`/customer/${c.id}`)}><Card><Text style={styles.name}>{c.company_name}</Text><Text style={styles.meta}>{c.customer_code} · {c.city}/{c.district}</Text>{c.contact_phone?<Text style={styles.phone}>{c.contact_name} · {c.contact_phone}</Text>:null}</Card></Pressable>):<Empty text="Bu filtrede musteri bulunamadi."/>}</Screen>}
const styles=StyleSheet.create({name:{fontSize:17,fontWeight:'700',color:colors.navy},meta:{color:colors.muted},phone:{color:colors.text}});
