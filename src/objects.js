import _ from 'lodash';
import moment from 'moment';
import { Linking } from 'react-native';
import { SERVER_URL } from './serviceApi';
import { btoa, getYoutubeVideoIdFromUrl } from './utils';

export class Season {
  constructor(attributes) { this.attributes = attributes; }

  get id() { return this.attributes.SeasonId; }
  get title() { return this.attributes.Title; }
}

export class Tournament {
  constructor(attributes) { this.attributes = attributes; }

  get id() { return this.attributes.TournamentId; }
  get title() { return this.attributes.Title; }
}

export class Advertisement {
  constructor(attributes) {
    this.attributes = attributes;
    this.cacheBurster = _.random(5000);
  }

  get id() { return this.attributes.AdvertisementId; }
  get url() { return `${SERVER_URL}/binder/ads/${this.id}.jpg?${this.cacheBurster}`; }
  get target() { return this.attributes.LinkAddress; }

  openTarget = () => this.target && Linking.openURL(this.target)

  static BigAd = 6;
  static SmallAd = 7;
}

export class GameMatch {

  constructor(attributes) {
    const { Date: date, Hour, GameFutureId, SeasonId, Stadium, TournamentId, VersusTeam, VersusTeamAtHome, GamePresentId, GamePastId, ScoreAway, ScoreHome, Description, Subtitle, Title, banners, LinkAddress1, LinkAddress1Past } = attributes;

    const time = moment.tz(`${date} ${Hour}`, 'YYYY-MM-DD HH:mm:ss', 'America/Chihuahua').local().toDate();
    const id = GameFutureId;
    const detailsId = GamePresentId;
    const summaryId = GamePastId;
    const desc = Description || Title || Subtitle;
    const promoVideo = getYoutubeVideoIdFromUrl(LinkAddress1);
    const matchVideo = getYoutubeVideoIdFromUrl(LinkAddress1Past);

    this.attributes = { time, id, detailsId, summaryId, SeasonId, Stadium, TournamentId, VersusTeam, VersusTeamAtHome, ScoreAway, ScoreHome, desc, banners, promoVideo, matchVideo };
  }

  get viewMoreUrl() {
    return this.summaryId
      ? `${SERVER_URL}/perfil-partidos-resumen.php?${_.replace(btoa(`gt=${this.summaryId}`), '=', '')}`
      : `${SERVER_URL}/perfil-partidos-por-jugar.php?${_.replace(btoa(`gf=${this.id}`), '=', '')}`;
  }
  get bannerUrls() { return this.attributes.banners; }
  get teamLogoUrl() { return `${SERVER_URL}/binder/gamefuture/${this.id}-0.png`; }
  get id() { return this.attributes.id; }
  get summaryId() { return this.attributes.summaryId; }
  get detailsId() { return this.attributes.detailsId; }
  get seasonId() { return this.attributes.SeasonId; }
  get tournamentId() { return this.attributes.TournamentId; }
  get time() { return moment(this.attributes.time); }
  get stadium() { return this.attributes.Stadium; }
  get versusTeam() { return this.attributes.VersusTeam; }
  get versusTeamAtHome() { return this.attributes.VersusTeamAtHome; }
  get desc() { return this.attributes.desc; }
  get scoreAway() { return this.attributes.ScoreAway; }
  get scoreHome() { return this.attributes.ScoreHome; }
  get promoVideo() { return this.attributes.promoVideo; }
  get matchVideo() { return this.attributes.matchVideo; }

}

export class GameMatchDetails {
  constructor(attributes) { this.attributes = attributes; }

  get eventId() { return this.attributes.GameEventId; }
  get minute() { return this.attributes.Minute; }
  get desc() { return this.attributes.Description; }
}

export class TeamInfo {

  constructor(attributes) {
    const { nombreClub, JJ, JG, JE, JP, GF, GC, Diferencia, puntos, idClub, redDot } = attributes;
    const logoUrl = `${SERVER_URL}/assets/images/logos-equipos/ascenso/tabla/${_.toLower(_.deburr(nombreClub)).replace(/[ .]+/g, '')}.png`;
    this.attributes = { nombreClub, logoUrl, JJ, JG, JE, JP, GF, GC, Diferencia, puntos, idClub, redDot };
  }

  get id() { return this.attributes.idClub; }
  get logoUrl() { return this.attributes.logoUrl; }
  get name() { return this.attributes.nombreClub; }
  get jj() { return this.attributes.JJ; }
  get jg() { return this.attributes.JG; }
  get je() { return this.attributes.JE; }
  get jp() { return this.attributes.JP; }
  get gf() { return this.attributes.GF; }
  get gc() { return this.attributes.GC; }
  get dif() { return this.attributes.Diferencia; }
  get pts() { return this.attributes.puntos; }
  get redDot() { return this.attributes.redDot }
}