import OneSignal from 'react-native-onesignal';
import { catchError } from './utils';
import { ServiceApi } from 'fc_juarez/src/serviceApi';

export const updatePushSettings = (settingName, value) => catchError((dispatch, getState) => {
  dispatch({ type: 'PUSH_SETTINGS_CHANGED', settingName, value });
  ServiceApi.updatePushSettings(getState().pushSettings);
}, 'No se han podido actualizar las preferencias');
export const downloadPushSettings = () => catchError(async (dispatch, getState) => {
  const newSettings = await ServiceApi.downloadPushSettings();
  dispatch({ type: 'PUSH_SETTINGS_CHANGED', state: newSettings });
  return getState().pushSettings;
}, 'No se han podido descargar las preferencias');
export const initializePushSettings = () => catchError((dispatch, getState) => {
  dispatch({ type: 'PUSH_SETTINGS_CHANGED', state: { receiveGeneralAlerts: true, receiveGoalsAlerts: true, receiveMatchAlerts: true } });
  ServiceApi.updatePushSettings(getState().pushSettings);
}, 'No se ha podido registrar el dispositivo para recibir notificaciones');
export const getPushPermissions = () => catchError(async (dispatch) => {
  const state = await new Promise((res) => OneSignal.getPermissionSubscriptionState(res));
  dispatch({ type: 'PUSH_PERMISSIONS_CHANGED', state });
  return state;
}, 'No se han podido leer los permisos de las notificaciones');