import _ from 'lodash';
import moment from 'moment';
import OneSignal from 'react-native-onesignal';
import { utf8ArrayToStr, CacheableImage } from 'fc_juarez/src/utils';

export const SERVER_URL = 'http://fcjuarez.com';
const API_PATH = '/api.php';

const SEASON_URL = '/Season?filter=Active,eq,1&columns=Title,SeasonId';
const TOURNAMENT_URL = '/Tournament?filter=Active,eq,1&columns=Title,TournamentId';
const GAME_MATCH_URL = `/GameFuture?columns=GameFutureId,SeasonId,TournamentId,Date,Hour,VersusTeam,VersusTeamAtHome,Stadium&filter[]=Active,eq,1&filter[]=Date,ge,${moment().subtract(4, 'months').format('YYYY-MM-DD')}`;
const GAME_MATCH_RESULTS_URL = (ids) => `/GamePresent?columns=GamePresentId,GameFutureId,ScoreHome,ScoreAway&filter=GameFutureId,in,${_.join(ids)}`;
const GAME_MATCH_SUMMARY_URL = (ids) => `/GamePast?columns=GamePastId,GameFutureId&filter=GameFutureId,in,${_.join(ids)}`;
const GAME_MATCH_DETAILS_URL = (id) => `/GameFuture/${id}`;
const GAME_MATCH_MINUTE_URL = (id) => `/GamePresentMinute?columns=GamePresentId,GameEventId,Minute,Description&filter=GamePresentId,eq,${id}`;
const WELCOME_BANNER_URL = '/Banner?order=InputDate,desc&page=1,1&columns=BannerId';
const ADS_URL = '/Advertisement?filter[]=AdvertisementId,in,6,7&filter[]=Active,eq,1&columns=AdvertisementId,LinkAddress,Active';
const GENERAL_TABLE_URL = 'http://administrador.ligamx.net/webservices/prtl_web_jsondata.ashx?psWidget=PRTL_EstdClubDtll&objIDDivision=2&objIDTemporada=68&objIDTorneo=1';

const normalizeData = ({ columns, records }) => _.map(records, (record) => _.zipObject(columns, record));
const fetchJson = async (url, decode = true) => {
  __DEV__ && console.log(`Fetching ${url}`);
  const response = await fetch(url, { headers: { 'Cache-Control': 'no-cache' } });
  let text = await response.text();
  if (decode) {
    text = utf8ArrayToStr(_.map(JSON.stringify(JSON.parse(text)), (c) => c.codePointAt(0)));
  }
  const result = JSON.parse(text);
  return result;
};
const fileExists = async (url) => {
  const result = await fetch(url, { method: 'HEAD', headers: { 'Cache-Control': 'no-cache' } });
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

    const [response2, response3] = await Promise.all([
      fetchJson(`${SERVER_URL}${API_PATH}${GAME_MATCH_RESULTS_URL(ids)}`), // eslint-disable-line new-cap
      fetchJson(`${SERVER_URL}${API_PATH}${GAME_MATCH_SUMMARY_URL(ids)}`), // eslint-disable-line new-cap
    ]);

    const matchesResults = _.keyBy(normalizeData(response2.GamePresent), 'GameFutureId');
    const summaryResults = _.keyBy(normalizeData(response3.GamePast), 'GameFutureId');

    _.each(matches, (match) => {
      const matchResult = matchesResults[match.GameFutureId];
      const summaryResult = summaryResults[match.GameFutureId];
      if (matchResult) _.assign(match, matchResult);
      if (summaryResult) _.assign(match, summaryResult);
    });

    return matches;
  }

  static async downloadFullMatch(id) {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${GAME_MATCH_DETAILS_URL(id)}`); // eslint-disable-line new-cap

    if (response) {
      const id = response['GameFutureId'];
      let banners = _.times(6, (num) => `${SERVER_URL}/binder/gamefuture/${id}-${num + 1}.jpg?${_.random(5000)}`);
      banners = _.map(banners, async (url) => ({ url, exists: await fileExists(url) }));
      banners = await Promise.all(banners);
      banners = _(banners).filter('exists').map('url').value();
      response.banners = banners;
      await Promise.all(_.map(banners, CacheableImage.cacheFile));
    }

    return response;
  }

  static async downloadMatchDetails(detailsId) {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${GAME_MATCH_MINUTE_URL(detailsId)}`); // eslint-disable-line new-cap
    return normalizeData(response.GamePresentMinute);
  }

  static async downloadWelcomeBanner() {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${WELCOME_BANNER_URL}`);
    const id = _.get(normalizeData(response.Banner), '[0].BannerId');
    const url = `${SERVER_URL}/binder/banner/${id}.jpg?${_.random(5000)}`;

    if (await fileExists(url)) {
      await CacheableImage.cacheFile(url);
      return url;
    }

    return null;
  }

  static async downloadAdvertisements() {
    const response = await fetchJson(`${SERVER_URL}${API_PATH}${ADS_URL}`);
    return normalizeData(response.Advertisement);
  }

  static async downloadGeneralTableData() {
    const response = await fetchJson(GENERAL_TABLE_URL, false);
    return response.DatosJSON;
  }

  static updatePushSettings(settings) {
    OneSignal.sendTags(_.mapValues(settings, (value) => `${Number(value)}`));
  }

  static async downloadPushSettings() {
    let tags = new Promise((res) => OneSignal.getTags(res));
    const timeout = new Promise((res) => setTimeout(res, 5000));
    tags = await Promise.race([tags, timeout]) || {};
    return _.mapValues(tags, (str) => !!Number(str));
  }

}