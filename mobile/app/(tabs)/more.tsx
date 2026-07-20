import { Ionicons } from '@expo/vector-icons';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { router } from 'expo-router';
import { Alert, Pressable, StyleSheet, Text, View } from 'react-native';
import { useAuth } from '@/auth';
import { Button, Card, Header, Screen, SectionTitle, StatusBadge } from '@/components';
import { acceptServerValues, discardOutbox, outboxItems, synchronize } from '@/outbox';
import { colors } from '@/theme';

type Module={title:string;path:string;icon:keyof typeof Ionicons.glyphMap;visible:boolean};
export default function More(){
  const{session,logout}=useAuth();const client=useQueryClient();const pending=useQuery({queryKey:['outbox'],queryFn:outboxItems});
  const permissions=session?.user.permissions??[];const can=(...items:string[])=>items.some(item=>permissions.includes(item));
  const modules = ([
    {title:'Personel',path:'/employees',icon:'people-outline',visible:can('employees.view')},
    {title:'Görevlerim',path:'/tasks',icon:'checkbox-outline',visible:true},
    {title:'Stok ve depo',path:'/inventory',icon:'cube-outline',visible:can('stock.manage')},
    {title:'Alış siparişleri',path:'/purchases',icon:'cart-outline',visible:can('purchases.manage','purchases.create','purchases.receive')},
    {title:'Tedarikçiler',path:'/suppliers',icon:'business-outline',visible:can('purchases.manage','suppliers.manage')},
    {title:'Kategoriler',path:'/categories',icon:'grid-outline',visible:can('products.manage')},
    {title:'Fiyat grupları',path:'/pricing',icon:'pricetags-outline',visible:can('products.manage')},
    {title:'Primler',path:'/commissions',icon:'trophy-outline',visible:can('commissions.view-own','commissions.view-all','commissions.manage')},
    {title:'Raporlar',path:'/reports',icon:'bar-chart-outline',visible:can('reports.view')},
    {title:'Bildirimler',path:'/notifications',icon:'notifications-outline',visible:true},
    {title:'Kullanım rehberi',path:'/guide',icon:'help-circle-outline',visible:true},
  ] satisfies Module[]).filter(x=>x.visible);
  async function refresh(){await pending.refetch();await client.invalidateQueries();}
  async function sync(){const result=await synchronize();await refresh();Alert.alert('Senkronizasyon tamamlandı',`${result.sent} işlem gönderildi, ${result.blocked} işlem kullanıcı kararı bekliyor.`);}
  async function accept(id:string){await acceptServerValues(id);const result=await synchronize();await refresh();Alert.alert(result.sent?'Yeni fiyat kabul edildi':'Tekrar gönderilemedi',result.sent?'Belge güncel sunucu fiyatlarıyla kaydedildi.':'Bağlantıyı kontrol edip yeniden deneyin.');}
  async function discard(id:string){Alert.alert('Yerel kayıt silinsin mi?','Bu işlem geri alınamaz.',[{text:'Vazgeç',style:'cancel'},{text:'Sil',style:'destructive',onPress:()=>void discardOutbox(id).then(refresh)}]);}
  return <Screen><Header eyebrow="Hesap ve araçlar" title="Daha fazla"/><Card accent><View style={styles.profile}><View style={styles.avatar}><Text style={styles.avatarText}>{session?.user.employee.name.slice(0,1)}</Text></View><View style={styles.flex}><Text style={styles.title}>{session?.user.employee.name}</Text><Text style={styles.meta}>{session?.user.email}</Text></View><StatusBadge label={session?.user.groups[0]??''}/></View></Card><SectionTitle title="Modüller"/>{modules.map(item=><Pressable accessibilityRole="button" key={item.path} onPress={()=>router.push(item.path as never)}><Card><View style={styles.module}><View style={styles.icon}><Ionicons name={item.icon} size={22} color={colors.orange}/></View><Text style={styles.link}>{item.title}</Text><Ionicons name="chevron-forward" size={20} color={colors.muted}/></View></Card></Pressable>)}<SectionTitle title="Çevrimdışı kuyruk" detail={`${pending.data?.length??0} bekleyen`}/><Card><Text style={styles.meta}>Taslaklar yalnızca sizin komutunuzla merkeze gönderilir.</Text><Button title="Senkronize et" icon="sync" onPress={sync} disabled={!pending.data?.length}/></Card>{pending.data?.map(item=><Card key={item.id}><Text style={styles.title}>{item.operation}</Text><Text style={styles.meta}>{new Date(item.created_at).toLocaleString('tr-TR')}</Text>{item.error_message?<Text style={styles.error}>{item.error_message}</Text>:<StatusBadge label="Gönderilmeyi bekliyor" tone="warning"/>}{item.error_code==='PRICE_CHANGED'?<Button title="Yeni fiyatı kabul et" onPress={()=>accept(item.id)}/>:null}{item.state==='blocked'?<Button title="Yerel kaydı sil" danger onPress={()=>discard(item.id)}/>:null}</Card>)}<Card><Text style={styles.title}>FORMMIX Android</Text><Text style={styles.meta}>Sürüm 0.1.0 · API v1</Text><Button title="Güvenli çıkış" icon="log-out-outline" secondary onPress={()=>logout()}/></Card></Screen>;
}
const styles=StyleSheet.create({profile:{flexDirection:'row',alignItems:'center',gap:12},avatar:{width:48,height:48,borderRadius:16,alignItems:'center',justifyContent:'center',backgroundColor:colors.navy},avatarText:{color:'#fff',fontSize:21,fontWeight:'900'},flex:{flex:1},module:{flexDirection:'row',alignItems:'center',gap:12,minHeight:42},icon:{width:42,height:42,borderRadius:14,alignItems:'center',justifyContent:'center',backgroundColor:colors.orangeSoft},title:{fontSize:17,fontWeight:'900',color:colors.navy},link:{flex:1,fontSize:16,fontWeight:'800',color:colors.navy},meta:{color:colors.muted},error:{color:colors.danger,lineHeight:20}});
