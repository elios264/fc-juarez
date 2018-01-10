import { combineReducers } from 'redux';
import { createCRUDObjectReducer, createSingleObjectReducer } from './utils';

const initializing = (state = false, action) => action.type === 'INITIALIZING' ? action.running : state;

const appInfo = (state = { isConnected: false, appState: 'unknown', pushPermissions: undefined, pushSettings: { receiveMatchAlerts: false, receiveGoalsAlerts: false, receiveGeneralAlerts: false } }, action) => {
  switch (action.type) {
    case 'NETWORK_CHANGED': return { ...state, isConnected: action.isConnected };
    case 'APPSTATE_CHANGED': return { ...state, appState: action.state };
    case 'PUSH_PERMISSIONS_CHANGED': return { ...state, pushPermissions: action.state };
    case 'PUSH_SETTINGS_CHANGED': return { ...state, pushSettings: action.state };
    default: return state;
  }
};

const objects = combineReducers({
  seasons: createCRUDObjectReducer('Season'),
  tournaments: createCRUDObjectReducer('Tournament'),
  gameMatches: createCRUDObjectReducer('GameMatch'),
  teamsInfo: createCRUDObjectReducer('TeamInfo'),
  nextMatch: createSingleObjectReducer('NextMatch'),
  currentMatch: createSingleObjectReducer('CurrentMatch'),
  welcomeBannerUrl: createSingleObjectReducer('WelcomeBannerUrl')
});


export const rootReducer = combineReducers({
  initializing,
  appInfo,
  objects
});
