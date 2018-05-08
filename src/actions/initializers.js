import _ from 'lodash';
import moment from 'moment';
import { AsyncStorage } from 'react-native';
import SplashScreen from 'react-native-splash-screen';

import { catchError } from './utils';
import { downloadPushSettings, initializePushSettings } from './pushNotifications';
import { appStart } from './appState';
import { ServiceApi } from 'fc_juarez/src/serviceApi';
import { GameMatch, GameMatchDetails, Season, Tournament, TeamInfo, Advertisement } from 'fc_juarez/src/objects';

export const intialize = () => catchError(async(dispatch) => {
  dispatch({ type: 'INITIALIZING', running: true });

  dispatch(appStart());

  const success = await dispatch(loadFromServer());
  if (!success) {
    await dispatch(loadFromStorage());
  }

  const pushSettings = await dispatch(downloadPushSettings());
  if (_.isEmpty(pushSettings)) {
    await dispatch(initializePushSettings());
  }

  SplashScreen.hide();
  dispatch({ type: 'INITIALIZING', running: false });
}, 'Ha ocurrido un error inicializando el sistema', (dispatch) => {
  SplashScreen.hide();
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

  if (nextMatch)
    dispatch({ type: 'NextMatch_FETCHED', object: _.create(GameMatch.prototype, nextMatch) });

  if (currentMatch && currentMatch.details)
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
  dispatch({ type: 'REFRESHING', refreshing: true });

  const [seasons, tournaments, gameMatches, welcomeBannerUrl, generalTableData, advertisement] = await Promise.all([
    ServiceApi.downloadSeasons(),
    ServiceApi.downloadTournaments(),
    ServiceApi.downloadGameMatches(),
    ServiceApi.downloadWelcomeBanner(),
    ServiceApi.downloadGeneralTableData(),
    ServiceApi.downloadAdvertisements(),
  ]);

  dispatch({ type: 'Season_FETCHED', objects: _.map(seasons, (attributes) => new Season(attributes)) });
  dispatch({ type: 'Tournament_FETCHED', objects: _.map(tournaments, (attributes) => new Tournament(attributes)) });
  dispatch({ type: 'GameMatch_FETCHED', objects: _.map(gameMatches, (attributes) => new GameMatch(attributes)) });
  dispatch({ type: 'TeamInfo_FETCHED', objects: _.map(generalTableData, (attributes) => new TeamInfo(attributes)) });
  dispatch({ type: 'Advertisement_FETCHED', objects: _.map(advertisement, (attributes) => new Advertisement(attributes)) });
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
  dispatch({ type: 'REFRESHING', refreshing: false });
  return true;
}, 'No se han podido descargar los datos del servidor:\nVerifica tu conexión de internet.', (dispatch) => {
  dispatch({ type: 'REFRESHING', refreshing: false });
  return false;
});