import { AppState } from 'react-native';

const subscribeToAppStateChanged = (handler) => {
  handler(AppState.currentState);
  AppState.addEventListener('change', handler);
  return () => AppState.removeEventListener('change', handler);
};

export const appStart = () => (dispatch) => {
  const appStateListener = (state) => {
    __DEV__ && console.log({ type: 'APPSTATE_CHANGED', state });
    dispatch({ type: 'APPSTATE_CHANGED', state });
  };

  subscribeToAppStateChanged(appStateListener);
};
