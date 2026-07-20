import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { Stack } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { useState } from 'react';
import { AuthProvider } from '@/auth';
import { colors } from '@/theme';

export default function RootLayout() {
  const [client] = useState(() => new QueryClient({ defaultOptions: { queries: { staleTime: 30_000, retry: 2 } } }));
  return <QueryClientProvider client={client}><AuthProvider><StatusBar style="light"/><Stack screenOptions={{headerStyle:{backgroundColor:colors.navy},headerTintColor:'#fff',headerTitleStyle:{fontWeight:'700'}}}><Stack.Screen name="index" options={{headerShown:false}}/><Stack.Screen name="login" options={{headerShown:false}}/><Stack.Screen name="(tabs)" options={{headerShown:false}}/><Stack.Screen name="customer/[id]" options={{title:'Musteri'}}/><Stack.Screen name="customer/new" options={{title:'Yeni musteri'}}/><Stack.Screen name="customer/[id]/activity" options={{title:'Gorusme ekle'}}/><Stack.Screen name="sales/new" options={{title:'Yeni satis belgesi'}}/><Stack.Screen name="sales/[id]" options={{title:'Belge ayrintisi'}}/></Stack></AuthProvider></QueryClientProvider>;
}
