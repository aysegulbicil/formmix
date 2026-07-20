import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import Constants from 'expo-constants';
import { router } from 'expo-router';
import { Platform } from 'react-native';
import { api } from './api';
Notifications.setNotificationHandler({handleNotification:async()=>({shouldShowBanner:true,shouldShowList:true,shouldPlaySound:true,shouldSetBadge:true})});
function openNotification(data:Record<string,unknown>|undefined){const route=typeof data?.route==='string'?data.route:'';if(route.startsWith('/'))router.push(route as never);}
export function configureNotificationNavigation():()=>void{const subscription=Notifications.addNotificationResponseReceivedListener(response=>openNotification(response.notification.request.content.data));void Notifications.getLastNotificationResponseAsync().then(response=>{if(response)openNotification(response.notification.request.content.data);});return()=>subscription.remove();}
export async function registerPushToken():Promise<void>{if(!Device.isDevice)return;if(Platform.OS==='android')await Notifications.setNotificationChannelAsync('default',{name:'FORMMIX',importance:Notifications.AndroidImportance.HIGH,vibrationPattern:[0,250,250,250],lightColor:'#F47A20'});let permission=await Notifications.getPermissionsAsync();if(permission.status!=='granted')permission=await Notifications.requestPermissionsAsync();if(permission.status!=='granted'){await api('/devices/push-token',{method:'PUT',body:JSON.stringify({enabled:false})});return;}try{const projectId=String(Constants.expoConfig?.extra?.easProjectId??'');if(!projectId)return;const token=(await Notifications.getExpoPushTokenAsync({projectId})).data;await api('/devices/push-token',{method:'PUT',body:JSON.stringify({push_token:token,enabled:true,app_version:'0.1.0'})});}catch{/* Firebase/EAS proje kimligi eklenene kadar uygulama ici bildirim kutusu calisir. */}}
