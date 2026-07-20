import { Ionicons } from '@expo/vector-icons';
import { Redirect, Tabs } from 'expo-router';
import { useEffect } from 'react';
import { useAuth } from '@/auth';
import { configureNotificationNavigation, registerPushToken } from '@/notifications';
import { checkForUpdate } from '@/releases';
import { colors } from '@/theme';
export default function TabsLayout(){const{session}=useAuth();useEffect(()=>configureNotificationNavigation(),[]);useEffect(()=>{if(session){void registerPushToken();void checkForUpdate();}},[session]);if(!session)return<Redirect href="/login"/>;return <Tabs screenOptions={{headerStyle:{backgroundColor:colors.navy},headerTintColor:'#fff',tabBarActiveTintColor:colors.orange,tabBarInactiveTintColor:colors.muted,tabBarStyle:{height:64,paddingBottom:8,paddingTop:6}}}><Tabs.Screen name="index" options={{title:'Ana sayfa',tabBarIcon:({color,size})=><Ionicons name="home" color={color} size={size}/>}}/><Tabs.Screen name="customers" options={{title:'Musteriler',tabBarIcon:({color,size})=><Ionicons name="people" color={color} size={size}/>}}/><Tabs.Screen name="products" options={{title:'Urunler',tabBarIcon:({color,size})=><Ionicons name="cube" color={color} size={size}/>}}/><Tabs.Screen name="orders" options={{title:'Satis',tabBarIcon:({color,size})=><Ionicons name="receipt" color={color} size={size}/>}}/><Tabs.Screen name="more" options={{title:'Daha fazla',tabBarIcon:({color,size})=><Ionicons name="menu" color={color} size={size}/>}}/></Tabs>}
