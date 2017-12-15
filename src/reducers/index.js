import { combineReducers } from 'redux';
//import { createCRUDObjectReducer } from './utils';

const initializing = (state = false, action) => action.type === 'INITIALIZING' ? action.running : state;

const appInfo = (state = { isConnected: false, appState: 'unknown' }, action) => {
  switch (action.type) {
    case 'NETWORK_CHANGED': return { ...state, isConnected: action.isConnected };
    case 'APPSTATE_CHANGED': return { ...state, appState: action.state };
    default: return state;
  }
};

export const rootReducer = combineReducers({
  initializing,
  appInfo,
});
