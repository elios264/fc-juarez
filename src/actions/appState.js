import { AppState, NetInfo } from 'react-native';
import OneSignal from 'react-native-onesignal';


const subscribeToNetworkChanged = async (handler) => {
  NetInfo.isConnected.addEventListener('connectionChange', handler);
  handler(await NetInfo.isConnected.fetch());
  return () => NetInfo.isConnected.removeEventListener('connectionChange', handler);
};
const subscribeToAppStateChanged = (handler) => {
  handler(AppState.currentState);
  AppState.addEventListener('change', handler);
  return () => AppState.removeEventListener('change', handler);
};

export const appStart = () => async (dispatch) => {
  const networkListener = (isConnected) => {
    isConnected = !!isConnected;
    __DEV__ && console.log({ type: 'NETWORK_CHANGED', isConnected });
    dispatch({ type: 'NETWORK_CHANGED', isConnected });
  };
  const appStateListener = (state) => {
    __DEV__ && console.log({ type: 'APPSTATE_CHANGED', state });
    dispatch({ type: 'APPSTATE_CHANGED', state });
  };
  const permissionsHandler = (state) => {
    __DEV__ && console.log({ type: 'PUSH_PERMISSIONS_CHANGED', state });
    dispatch({ type: 'PUSH_PERMISSIONS_CHANGED', state });
  };

  const task = subscribeToNetworkChanged(networkListener);
  subscribeToAppStateChanged(appStateListener),
  OneSignal.getPermissionSubscriptionState(permissionsHandler);

  await task;
};