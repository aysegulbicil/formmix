import { useQuery } from '@tanstack/react-query';
import { router } from 'expo-router';
import { Pressable, StyleSheet, Text } from 'react-native';
import { api } from '@/api';
import { Button, Card, Empty, Header, Loading, Screen } from '@/components';
import { colors } from '@/theme';
type Document={id:number;document_number:string;document_type:'quote'|'order';company_name:string;status:string;grand_total:number};
export default function Orders(){const query=useQuery({queryKey:['sales-documents'],queryFn:()=>api<{data:Document[];meta:{total:number}}>('/sales-documents')});return <Screen><Header title="Teklif ve siparisler" subtitle={`${query.data?.meta.total??0} belge`} action={<Button title="+ Yeni" onPress={()=>router.push('/sales/new')}/>}/>{query.isLoading?<Loading/>:query.data?.data.length?query.data.data.map(d=><Pressable key={d.id} onPress={()=>router.push(`/sales/${d.id}`)}><Card><Text style={styles.title}>{d.document_number}</Text><Text style={styles.company}>{d.company_name}</Text><Text style={styles.meta}>{d.document_type==='quote'?'Teklif':'Siparis'} - {d.status}</Text><Text style={styles.total}>{new Intl.NumberFormat('tr-TR',{style:'currency',currency:'TRY'}).format(Number(d.grand_total))}</Text></Card></Pressable>):<Empty text="Teklif veya siparis bulunamadi."/>}</Screen>}
const styles=StyleSheet.create({title:{fontWeight:'800',color:colors.navy,fontSize:17},company:{color:colors.text},meta:{color:colors.muted},total:{color:colors.orange,fontWeight:'800',fontSize:18}});
