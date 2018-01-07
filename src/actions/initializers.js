import _ from 'lodash';
import moment from 'moment';

import { catchError } from './utils';
import { appStart } from './appState';
import { ServiceApi } from 'fc_juarez/src/serviceApi';
import { GameMatch, GameMatchDetails, Season, Tournament, TeamInfo } from 'fc_juarez/src/objects';


export const intialize = () => catchError(async(dispatch, getState) => {
  dispatch({ type: 'INITIALIZING', running: true });

  await dispatch(appStart());
  await dispatch(getState().appInfo.isConnected ? loadFromServer() : loadFromStorage());

  dispatch({ type: 'INITIALIZING', running: false });
}, 'Ha ocurrido un error inicializando el sistema', (dispatch) => {
  dispatch({ type: 'INITIALIZING', running: false });
});

export const loadFromStorage = () => catchError(() => {

  console.log('Leyendo de almacenamiento');

}, 'No se han podido recargar los datos del almacenamiento interno');

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
  dispatch({ type: 'WelcomeBannerUrl_FETCHED', object: welcomeBannerUrl });
  dispatch({ type: 'TeamInfo_FETCHED', objects: _.map(generalTableData, (attributes) => new TeamInfo(attributes)) });

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

}, 'No se han podido descargar los datos del servidor');