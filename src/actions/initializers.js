import _ from 'lodash';
import moment from 'moment';
import { AsyncStorage } from 'react-native';

import { catchError } from './utils';
import { appStart } from './appState';
import { ServiceApi } from 'fc_juarez/src/serviceApi';
import { GameMatch, GameMatchDetails, Season, Tournament, TeamInfo } from 'fc_juarez/src/objects';

export const intialize = () => catchError(async(dispatch, getState) => {
  dispatch({ type: 'INITIALIZING', running: true });

  await dispatch(appStart());

  if (getState().appInfo.isConnected) {
    await dispatch(loadFromServer());
    const settings = await dispatch(downloadPushSettings());
    if (_.isEmpty(settings)) await dispatch(initializePushSettings());
  } else {
    await dispatch(loadFromStorage());
  }


  dispatch({ type: 'INITIALIZING', running: false });
}, 'Ha ocurrido un error inicializando el sistema', (dispatch) => {
  dispatch({ type: 'INITIALIZING', running: false });
});

export const loadFromStorage = () => catchError(async (dispatch) => {
  const json = await AsyncStorage.getItem('offline-match-data');
  if (!json) throw new Error('No hay datos previamente almacenados');
  const { seasons, currentMatch, gameMatches, nextMatch, teamsInfo, tournaments, welcomeBannerUrl } = JSON.parse(json);

  dispatch({ type: 'Season_FETCHED', objects: _.map(seasons, (attributes) => _.create(Season.prototype, attributes)) });
  dispatch({ type: 'Tournament_FETCHED', objects: _.map(tournaments, (attributes) => _.create(Tournament.prototype, attributes)) });
  dispatch({ type: 'GameMatch_FETCHED', objects: _.map(gameMatches, (attributes) => _.create(GameMatch.prototype, attributes)) });
  dispatch({ type: 'TeamInfo_FETCHED', objects: _.map(teamsInfo, (attributes) => _.create(TeamInfo.prototype, attributes)) });
  dispatch({ type: 'WelcomeBannerUrl_FETCHED', object: welcomeBannerUrl });
  dispatch({ type: 'NextMatch_FETCHED', object: _.create(GameMatch.prototype, nextMatch) });
  dispatch({ type: 'CurrentMatch_FETCHED', object: {
    match: _.create(GameMatch.prototype, currentMatch.match),
    details: _.map(currentMatch.details, (details) => _.create(GameMatchDetails.prototype, details))
  } });

}, 'No se han podido recargar los datos del almacenamiento interno');

export const saveToStorage = () => catchError(async (dispatch, getState) => {
  const data = getState().objects;
  const jsonData = JSON.stringify(data);
  await AsyncStorage.setItem('offline-match-data', jsonData);
}, 'No se han podido guardar los datos en el almacenamiento interno para uso offline');

export const loadFromServer = () => catchError(async (dispatch, getState) => {
  const [seasons, tournaments, gameMatches, welcomeBannerUrl, generalTableData] = await Promise.all([
    ServiceApi.downloadSeasons(),
    ServiceApi.downloadTournaments(),
    ServiceApi.downloadGameMatches(),
    ServiceApi.downloadWelcomeBanner(),
    ServiceApi.downloadGeneralTableData(),
  ]);

  dispatch({ type: 'Season_FETCHED', objects: _.map(seasons, (attributes) => new Season(attributes)) });
  dispatch({ type: 'Tournament_FETCHED', objects: _.map(tournaments, (attributes) => new Tournament(attributes)) });
  dispatch({ type: 'GameMatch_FETCHED', objects: _.map(gameMatches, (attributes) => new GameMatch(attributes)) });
  dispatch({ type: 'TeamInfo_FETCHED', objects: _.map(generalTableData, (attributes) => new TeamInfo(attributes)) });
  dispatch({ type: 'WelcomeBannerUrl_FETCHED', object: welcomeBannerUrl });

  const current = moment();
  const matches = _.orderBy(getState().objects.gameMatches, 'time');
  const nextMatch = _.find(matches, ({ time }) => current.isBefore(time));
  const currentMatch = _(matches).filter('detailsId').findLast(({ time }) => moment(time).isBefore(current));

  const [fullNextMatch, currentMatchDetails] = await Promise.all([
    nextMatch ? ServiceApi.downloadFullMatch(nextMatch.id) : Promise.resolve(undefined),
    currentMatch ? ServiceApi.downloadMatchDetails(currentMatch.detailsId) : Promise.resolve(undefined)
  ]);

  if (fullNextMatch)
    dispatch({ type: 'NextMatch_FETCHED', object: new GameMatch(fullNextMatch) });

  if (currentMatchDetails) {
    const sortedDetails = _(currentMatchDetails).map((attributes) => new GameMatchDetails(attributes)).orderBy('minute', 'desc').value();
    dispatch({ type: 'CurrentMatch_FETCHED', object: { match: currentMatch, details: sortedDetails } });
  }

  dispatch(saveToStorage());
}, 'No se han podido descargar los datos del servidor');

export const updatePushSettings = (settingName, value) => catchError(async (dispatch) => {
  ServiceApi.updatePushSettings(settingName, value);
  await dispatch(downloadPushSettings());
}, 'No se han podido actualizar las preferencias');

export const downloadPushSettings = () => catchError(async (dispatch) => {
  const newSettings = await ServiceApi.downloadPushSettings();
  dispatch({ type: 'PUSH_SETTINGS_CHANGED', state: newSettings });
  return newSettings;
}, 'No se han podido descargar las preferencias');

export const initializePushSettings = () => catchError(async (dispatch) => {
  ServiceApi.updatePushSettings('receiveMatchAlerts', true);
  ServiceApi.updatePushSettings('receiveGoalsAlerts', true);
  ServiceApi.updatePushSettings('receiveGeneralAlerts', true);
  await dispatch(downloadPushSettings());
}, 'No se ha podido registrar el dispositivo para recibir notificaciones');