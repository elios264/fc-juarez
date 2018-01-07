import _ from 'lodash';
import moment from 'moment';
import { SERVER_URL } from './serviceApi';

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

export class GameMatch {

  constructor(attributes) {
    const { Date: date, Hour, GameFutureId, SeasonId, Stadium, TournamentId, VersusTeam, VersusTeamAtHome, GamePresentId, ScoreAway, ScoreHome, Description, Subtitle, Title } = attributes;

    const time = moment.tz(`${date} ${Hour}`, 'YYYY-MM-DD HH:mm:ss', 'America/Chihuahua').local().toDate();
    const id = GameFutureId;
    const detailsId = GamePresentId;
    const desc = Description || Title || Subtitle;

    this.attributes = { time, id, detailsId, SeasonId, Stadium, TournamentId, VersusTeam, VersusTeamAtHome, ScoreAway, ScoreHome, desc };
  }

  get id() { return this.attributes.id; }
  get detailsId() { return this.attributes.detailsId; }
  get seasonId() { return this.attributes.SeasonId; }
  get tournamentId() { return this.attributes.TournamentId; }
  get time() { return this.attributes.time; }
  get stadium() { return this.attributes.Stadium; }
  get versusTeam() { return this.attributes.VersusTeam; }
  get versusTeamAtHome() { return this.attributes.VersusTeamAtHome; }
  get desc() { return this.attributes.desc; }
  get scorAway() { return this.attributes.ScoreAway; }
  get scoreHome() { return this.attributes.ScoreHome; }

}

export class GameMatchDetails {
  constructor(attributes) { this.attributes = attributes; }

  get eventId() { return this.attributes.GameEventId; }
  get minute() { return this.attributes.Minute; }
  get desc() { return this.attributes.Description; }
}

export class TeamInfo {

  constructor(attributes) {
    const { nombreClub, JJ, JG, JE, JP, GF, GC, Diferencia, puntos, idClub } = attributes;
    const logoUrl = `${SERVER_URL}/assets/images/logos-equipos/ascenso/tabla/${_.toLower(_.deburr(nombreClub)).replace(/[ .]+/g, '')}.png`;
    this.attributes = { nombreClub, logoUrl, JJ, JG, JE, JP, GF, GC, Diferencia, puntos, idClub };
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
}