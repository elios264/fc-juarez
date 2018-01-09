import _ from 'lodash';
import moment from 'moment';
import { Image } from 'react-native';
const utf8 = require('fc_juarez/node_modules/utf8');

export const SERVER_URL = 'http://fcjuarez.com';
const API_PATH = '/api.php';

const SEASON_URL = '/Season?filter=Active,eq,1&columns=Title,SeasonId';
const TOURNAMENT_URL = '/Tournament?filter=Active,eq,1&columns=Title,TournamentId';
const GAME_MATCH_URL = `/GameFuture?columns=GameFutureId,SeasonId,TournamentId,Date,Hour,VersusTeam,VersusTeamAtHome,Stadium&filter[]=Active,eq,1&filter[]=Date,ge,${moment().subtract(4, 'months').format('YYYY-MM-DD')}`;
const GAME_MATCH_RESULTS_URL = (ids) => `/GamePresent?columns=GamePresentId,GameFutureId,ScoreHome,ScoreAway&filter=GameFutureId,in,${_.join(ids)}`;
const GAME_MATCH_DETAILS_URL = (id) => `/GameFuture/${id}`;
const GAME_MATCH_MINUTE_URL = (id) => `/GamePresentMinute?columns=GamePresentId,GameEventId,Minute,Description&filter=GamePresentId,eq,${id}`;
const WELCOME_BANNER_URL = '/Banner?order=InputDate,desc&page=1,1&columns=BannerId';
const GENERAL_TABLE_URL = 'http://administrador.ligamx.net/webservices/prtl_web_jsondata.ashx?psWidget=PRTL_EstdClubDtll&objIDDivision=2&objIDTemporada=68&objIDTorneo=1';

const normalizeData = ({ columns, records }) => _.map(records, (record) => _.zipObject(columns, record));
const fetchJson = async (url, decode = true) => {
  console.log(`Fetching ${url}`);
  const response = await fetch(url);
  let text = await response.text();
  if (decode)
    text = utf8.decode(JSON.stringify(JSON.parse(text)));
  return JSON.parse(text);
};
const fileExists = async (url) => {
  const result = await fetch(url, { method: 'HEAD' });
  return result.status !== 404;
};

export class ServiceApi {

  static async downloadSeasons() {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${SEASON_URL}`);
    return normalizeData(response.Season);
  }

  static async downloadTournaments() {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${TOURNAMENT_URL}`);
    return normalizeData(response.Tournament);
  }

  static async downloadGameMatches() {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${GAME_MATCH_URL}`);
    const matches = normalizeData(response.GameFuture);

    const ids = _.map(matches, 'GameFutureId');
    const response2 = await fetchJson(`${SERVER_URL}${API_PATH}${GAME_MATCH_RESULTS_URL(ids)}`); // eslint-disable-line new-cap
    const matchesResults = _.keyBy(normalizeData(response2.GamePresent), 'GameFutureId');

    _.each(matches, (match) => {
      const matchResult = matchesResults[match.GameFutureId];
      if (matchResult) _.assign(match, matchResult);
    });

    return matches;
  }

  static async downloadFullMatch(id) {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${GAME_MATCH_DETAILS_URL(id)}`); // eslint-disable-line new-cap
    return response;
  }

  static async downloadMatchDetails(detailsId) {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${GAME_MATCH_MINUTE_URL(detailsId)}`); // eslint-disable-line new-cap
    return normalizeData(response.GamePresentMinute);
  }

  static async downloadWelcomeBanner() {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${WELCOME_BANNER_URL}`);
    const id = _.get(normalizeData(response.Banner), '[0].BannerId');
    const url = `${SERVER_URL}/binder/banner/${id}.jpg`;

    if (await fileExists(url)) {
      await Image.prefetch(url);
      return url;
    }

    return null;
  }

  static async downloadGeneralTableData() {
    const response = await fetchJson(GENERAL_TABLE_URL, false);
    return response.DatosJSON;
  }

}