import { AppState, NetInfo } from 'react-native';

const subscribeToNetworkChanged = async (handler) => {
  NetInfo.isConnected.addEventListener('change', handler);
  handler(await NetInfo.isConnected.fetch());
  return () => NetInfo.isConnected.removeEventListener('change', handler);
};
const subscribeToAppStateChanged = (handler) => {
  handler(AppState.currentState);
  AppState.addEventListener('change', handler);
  return () => AppState.removeEventListener('change', handler);
};

export const appStart = () => async (dispatch) => {
  const networkListener = (isConnected) => {
    isConnected = !!isConnected;
    dispatch({ type: 'NETWORK_CHANGED', isConnected });
  };
  const appStateListener = (state) => {
    dispatch({ type: 'APPSTATE_CHANGED', state });
  };

  await subscribeToNetworkChanged(networkListener);
  await subscribeToAppStateChanged(appStateListener);
};