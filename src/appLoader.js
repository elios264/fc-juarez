import React from 'react';
import thunk from 'redux-thunk';
import { AppRegistry, StyleSheet } from 'react-native';
import { compose, createStore, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import { NativeRouter, Route } from 'react-router-native';
import NativeTachyons from 'react-native-style-tachyons';

import { name as appName } from 'fc_juarez/app.json';
import { fonts, palette, rem } from './theme';
import { intialize } from './actions/initializers';
import { rootReducer } from './reducers';

const moment = require('moment');
require('moment/locale/es');
require('moment-timezone');
moment.locale('es');

NativeTachyons.build({ rem, fonts, colors: { palette } }, StyleSheet);

const { App } = require('./components/app');
const composeEnhancers = __DEV__ ? (window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose) : compose;
const store = createStore(rootReducer, composeEnhancers(applyMiddleware(thunk)));

store.dispatch(intialize());


const AppBootstrapper = () => (
  <Provider store={store}>
    <NativeRouter>
      <Route component={App} />
    </NativeRouter>
  </Provider>
);


AppRegistry.registerComponent(appName, () => AppBootstrapper);