import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text, TouchableOpacity, TouchableHighlight, RefreshControl, Linking } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import _ from 'lodash';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { DataPicker } from 'rnkit-actionsheet-picker';
import moment from 'moment';
import ScalableImage from 'react-native-scalable-image';

import { getValue } from 'fcjuarez/src/utils';
import { loadFromServer } from 'fcjuarez/src/actions/initializers';
import { Season, GameMatch, Tournament, Advertisement } from 'fcjuarez/src/objects';

class _MatchInfo extends PureComponent {

  static propTypes = {
    tournament: PropTypes.instanceOf(Tournament).isRequired,
    match: PropTypes.instanceOf(GameMatch).isRequired,
    nextMatch: PropTypes.instanceOf(GameMatch),
  }

  openViewMore = () => {
    const { history, nextMatch, match } = this.props;
    const { viewMoreUrl, id } = match;

    if (_.get(nextMatch, 'id') === id)
      history.replace('/next-match');
    else
      Linking.openURL(viewMoreUrl);
  }

  buyTickets = () => {
    Linking.openURL('https://fcjuarez.boletosenlinea.events/');
  }



  render() {
    const { match, tournament, nextMatch } = this.props;
    const { time, stadium, scoreAway, scoreHome, versusTeam, versusTeamAtHome, teamLogoUrl } = match;


    const bravos = { name: 'FC Juárez', logo: require('fcjuarez/assets/img/fcjuarez.png') };
    const enemy = { name: versusTeam, logo: { uri: teamLogoUrl } };

    const fst = versusTeamAtHome ? enemy : bravos;
    const snd = versusTeamAtHome ? bravos : enemy;

    const now = moment();
    const mode = moment(time).isBefore(now)
      ? 'prev'
      : _.get(nextMatch, 'id') === match.id
        ? 'cur'
        : 'next';

    return (
      <View cls='aic mv3'>
        <View cls='flx-row aic jcc mb2'>
          <Text cls='white ff-ubu-b mr2 f6 tr flx-i bg-transparent'>{fst.name}</Text>
          <Image cls='pa1 rm-contain' style={[styles.logo]} source={fst.logo} />
          <Text cls='white ff-ubu-b mh2 bg-transparent'>{scoreHome}<Text cls='gray'> vs </Text>{scoreAway}</Text>
          <Image cls='pa1 rm-contain' style={[styles.logo]} source={snd.logo} />
          <Text cls='white ff-ubu-b ml2 f6 tl flx-i bg-transparent'>{snd.name}</Text>
        </View>
        <Text cls='white ff-ubu-b mb1 bg-transparent'>{_.capitalize(time.format('MMM/DD/YYYY').replace(/\./, ''))}<Text cls='gray'>  |  </Text>{time.format('hh:mm A')}</Text>
        <Text cls='contrast ff-ubu-b mb3 bg-transparent'>{stadium}<Text cls='gray'>  |  </Text>{tournament.title}</Text>
        <TouchableOpacity onPress={this.openViewMore} cls='bg-contrast pv2 jcc aic ass' activeOpacity={0.6} >
          <Text cls='white f6 ff-ubu-b bg-transparent'>{getValue(mode, { prev: 'Resumen' }, 'Previa')}</Text>
        </TouchableOpacity>
        {(mode === 'cur' || mode === 'next') &&
          <TouchableOpacity onPress={this.buyTickets} cls='ass mt2' activeOpacity={0.6} >
            <View cls='flx-row jcc aic h2' >
              <Image cls='absolute-fill rm-stretch' style={[styles.expand]} source={require('fcjuarez/assets/img/rectangle.png')} />
              <Text cls='white f6 ff-ubu-b bg-transparent'>Comprar Boletos</Text>
            </View>
          </TouchableOpacity>
        }
      </View>
    );
  }
}
export const MatchInfo = NativeTachyons.wrap(_MatchInfo);

const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
const mapStateToProps = (state) => ({
  nextMatch: state.objects.nextMatch,
  gameMatches: state.objects.gameMatches,
  seasons: _.values(state.objects.seasons),
  tournaments: state.objects.tournaments,
  ad: state.objects.ads[Advertisement.SmallAd],
  refreshing: state.refreshing
});
export class _MatchCalendar extends PureComponent {

  static propTypes = {
    loadFromServer: PropTypes.func.isRequired,
    seasons: PropTypes.arrayOf(PropTypes.instanceOf(Season)).isRequired,
    tournaments: PropTypes.objectOf(PropTypes.instanceOf(Tournament)).isRequired,
    gameMatches: PropTypes.objectOf(PropTypes.instanceOf(GameMatch)).isRequired,
    nextMatch: PropTypes.instanceOf(GameMatch),
    ad: PropTypes.instanceOf(Advertisement),
    refreshing: PropTypes.bool.isRequired,
  }

  state = { currentSeason: _.last(this.props.seasons) };

  openPicker = () => {
    const { seasons } = this.props;
    DataPicker.show({
      dataSource: _.map(seasons, 'title'),
      defaultSelected: [this.state.currentSeason.title],
      cancelText: 'Cancelar',
      doneText: 'Seleccionar',
      onPickerConfirm: (txt, idx) => { this.setState({ currentSeason: seasons[idx] }); },
      onPickerDidSelect: (txt, idx) => { this.setState({ currentSeason: seasons[idx] }); }
    });
  }


  render() {
    let { gameMatches, tournaments, ad, refreshing, loadFromServer, nextMatch, history } = this.props;
    const { currentSeason } = this.state;

    gameMatches = _(gameMatches)
      .filter(['seasonId', _.get(currentSeason, 'id', -1)])
      .sortBy(gameMatches, 'time')
      .value();

    return (
      <View cls='flx-i'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fcjuarez/assets/img/background.png')} />
          <ScrollView cls='flx-i' refreshControl={<RefreshControl refreshing={refreshing} onRefresh={loadFromServer} tintColor='white' />} >
            <View cls='aic mv3 mh2 flx-row jcsb'>
              <Text cls='f5 mr3 ff-ubu-m white bg-transparent flx-i'>Calendario <Text cls='#AAAAAA'>de partidos</Text></Text>
              <TouchableOpacity cls='jcc bg-rgba(13,13,13,0.8)' onPress={currentSeason ? this.openPicker : _.noop} activeOpacity={0.8} >
                <Text cls='ff-ubu-b white f6 ma2 mr5'>{currentSeason ? `Ver ${_.toLower(currentSeason.title)}` : 'Sin temporadas'}</Text>
                <View cls='absolute right-1' style={[styles.triangle]} />
              </TouchableOpacity>
            </View>
            <View cls='bt b--#373737' />
            <View cls='mh2 mv3'>
              <Text cls='mb2 white ff-ubu-b bg-transparent'>{currentSeason ? currentSeason.title : 'Sin temporadas'}</Text>
              {_.map(gameMatches, (match) => <MatchInfo key={match.id} nextMatch={nextMatch} match={match} tournament={tournaments[match.tournamentId]} history={history} />)}
            </View>
          </ScrollView>
        </View>
        <TouchableHighlight onPress={ad ? ad.openTarget : _.noop} >
          <ScalableImage width={Dimensions.get('window').width} source={ ad ? { uri: ad.url } : require('fcjuarez/assets/img/ads/smallAd.png')} />
        </TouchableHighlight>
      </View>
    );
  }
}

const styles = StyleSheet.create({
  expand: {
    width: '100%',
    height: '100%'
  },
  logo: {
    width: sizes.w2 + sizes.w1,
    height: sizes.h2 + sizes.h1,
  },
  triangle: {
    width: 0,
    height: 0,
    backgroundColor: 'transparent',
    borderStyle: 'solid',
    borderLeftWidth: 4,
    borderRightWidth: 4,
    borderBottomWidth: 6,
    borderLeftColor: 'transparent',
    borderRightColor: 'transparent',
    borderBottomColor: 'white',
    transform: [{ rotate: '180deg' }]
  }
});

export const MatchCalendar = connect(mapStateToProps, mapDispatchToProps)(NativeTachyons.wrap(_MatchCalendar));
