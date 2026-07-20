import { Alert, Linking } from 'react-native';
import { api } from './api';
type Release={version_name:string;version_code:number;minimum_version_code:number;download_url:string;release_notes?:string};
const CURRENT_VERSION_CODE=1;
export async function checkForUpdate():Promise<void>{try{const response=await api<{data:Release|null}>('/app/releases/current');const release=response.data;if(!release||release.version_code<=CURRENT_VERSION_CODE)return;const required=CURRENT_VERSION_CODE<release.minimum_version_code;Alert.alert(required?'Guncelleme zorunlu':`Yeni surum ${release.version_name}`,release.release_notes||'Yeni FORMMIX surumu hazir.',required?[{text:'APK\'yi indir',onPress:()=>void Linking.openURL(release.download_url)}]:[{text:'Daha sonra',style:'cancel'},{text:'APK\'yi indir',onPress:()=>void Linking.openURL(release.download_url)}],{cancelable:!required});}catch{/* Surum kontrolu is akisini engellemez. */}}
