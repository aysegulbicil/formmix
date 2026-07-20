import { Redirect } from 'expo-router';
import { Loading } from '@/components';
import { useAuth } from '@/auth';
export default function Index(){const{session,loading}=useAuth();if(loading)return<Loading/>;return<Redirect href={session?'/(tabs)':'/login'}/>;}
