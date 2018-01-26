import { AppState } from 'react-native';
import OneSignal from 'react-native-onesignal';

const subscribeToAppStateChanged = (handler) => {
  handler(AppState.currentState);
  AppState.addEventListener('change', handler);
  return () => AppState.removeEventListener('change', handler);
};

export const appStart = () => (dispatch) => {
  const permissionsHandler = (state) => {
    __DEV__ && console.log({ type: 'PUSH_PERMISSIONS_CHANGED', state });
    dispatch({ type: 'PUSH_PERMISSIONS_CHANGED', state });
  };
  const appStateListener = (state) => {
    __DEV__ && console.log({ type: 'APPSTATE_CHANGED', state });
    dispatch({ type: 'APPSTATE_CHANGED', state });
    if (state === 'active') OneSignal.getPermissionSubscriptionState(permissionsHandler);
  };

  subscribeToAppStateChanged(appStateListener),
  OneSignal.getPermissionSubscriptionState(permissionsHandler);
};