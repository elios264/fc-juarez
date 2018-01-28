import { combineReducers } from 'redux';
import { createCRUDObjectReducer, createSingleObjectReducer } from './utils';

const initializing = (state = false, action) => action.type === 'INITIALIZING' ? action.running : state;
const refreshing = (state = false, action) => action.type === 'REFRESHING' ? action.refreshing : state;

const pushSettings = (state = { receiveMatchAlerts: false, receiveGoalsAlerts: false, receiveGeneralAlerts: false }, action) => {
  if (action.type !== 'PUSH_SETTINGS_CHANGED')
    return state;

  if (action.state)
    return action.state;
  else
    return { ...state, [action.settingName]: action.value };
};

const appInfo = (state = { appState: 'unknown', pushPermissions: undefined }, action) => {
  switch (action.type) {
    case 'APPSTATE_CHANGED': return { ...state, appState: action.state };
    case 'PUSH_PERMISSIONS_CHANGED': return { ...state, pushPermissions: action.state };
    default: return state;
  }
};

const objects = combineReducers({
  seasons: createCRUDObjectReducer('Season'),
  tournaments: createCRUDObjectReducer('Tournament'),
  gameMatches: createCRUDObjectReducer('GameMatch'),
  teamsInfo: createCRUDObjectReducer('TeamInfo'),
  ads: createCRUDObjectReducer('Advertisement'),
  nextMatch: createSingleObjectReducer('NextMatch'),
  currentMatch: createSingleObjectReducer('CurrentMatch'),
  welcomeBannerUrl: createSingleObjectReducer('WelcomeBannerUrl')
});


export const rootReducer = combineReducers({
  initializing,
  refreshing,
  appInfo,
  objects,
  pushSettings
});
