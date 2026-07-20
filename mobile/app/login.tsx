import { zodResolver } from '@hookform/resolvers/zod';
import { Redirect, router } from 'expo-router';
import { Controller, useForm } from 'react-hook-form';
import { Image, KeyboardAvoidingView, Platform, StyleSheet, Text, View } from 'react-native';
import { z } from 'zod';
import { ApiError } from '@/api';
import { useAuth } from '@/auth';
import { Button, Field, Screen } from '@/components';
import { colors, spacing } from '@/theme';

const schema=z.object({email:z.email('Gecerli e-posta yazin.'),password:z.string().min(1,'Parola zorunlu.')}); type Form=z.infer<typeof schema>;
export default function Login(){const{session,login}=useAuth();const{control,handleSubmit,setError,formState:{errors,isSubmitting}}=useForm<Form>({resolver:zodResolver(schema),defaultValues:{email:'',password:''}});if(session)return<Redirect href="/(tabs)"/>;const submit=handleSubmit(async v=>{try{await login(v.email,v.password);router.replace('/(tabs)');}catch(e){setError('root',{message:e instanceof ApiError?e.message:'Baglanti kurulamadi.'});}});return <Screen><KeyboardAvoidingView behavior={Platform.OS==='ios'?'padding':undefined} style={styles.wrap}><View style={styles.brand}><Image source={require('../assets/logo.png')} style={styles.logo} resizeMode="contain"/><Text style={styles.tag}>Satis ve operasyon mobil paneli</Text></View><View style={styles.form}><Controller control={control} name="email" render={({field})=><Field label="E-posta" autoCapitalize="none" keyboardType="email-address" value={field.value} onChangeText={field.onChange} error={errors.email?.message}/>}/><Controller control={control} name="password" render={({field})=><Field label="Parola" secureTextEntry value={field.value} onChangeText={field.onChange} error={errors.password?.message}/>}/>{errors.root?.message?<Text style={styles.error}>{errors.root.message}</Text>:null}<Button title="Giris yap" onPress={submit} loading={isSubmitting}/></View></KeyboardAvoidingView></Screen>}
const styles=StyleSheet.create({wrap:{flex:1,justifyContent:'center',gap:spacing.xl},brand:{alignItems:'center',gap:spacing.md},logo:{width:220,height:75},tag:{color:colors.muted,fontSize:16},form:{gap:spacing.md},error:{color:colors.danger,textAlign:'center'}});
