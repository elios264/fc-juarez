import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text, TouchableOpacity, TouchableHighlight, RefreshControl, Linking } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';
import _ from 'lodash';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { loadFromServer } from 'fc_juarez/src/actions/initializers';
import { Season, GameMatch, Tournament } from 'fc_juarez/src/objects';
import { DataPicker } from 'rnkit-actionsheet-picker';

@NativeTachyons.wrap
class MatchInfo extends PureComponent {

  static propTypes = {
    tournament: PropTypes.instanceOf(Tournament).isRequired,
    match: PropTypes.instanceOf(GameMatch).isRequired,
  }


  openViewMore = () => {
    const { match: { viewMoreUrl } } = this.props;
    Linking.openURL(viewMoreUrl);
  }


  render() {
    const { match, tournament } = this.props;
    const { time, stadium, scoreAway, scoreHome, versusTeam, versusTeamAtHome, teamLogoUrl } = match;


    const bravos = { name: 'FC Juárez', logo: require('fc_juarez/assets/img/fcjuarez.png') };
    const enemy = { name: versusTeam, logo: { uri: teamLogoUrl } };

    const fst = versusTeamAtHome ? enemy : bravos;
    const snd = versusTeamAtHome ? bravos : enemy;

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
        <TouchableHighlight onPress={this.openViewMore} cls='bg-contrast pv2 jcc aic ass' underlayColor='#0c963e' >
          <Text cls='white f6 ff-ubu-b bg-transparent'>Ver más</Text>
        </TouchableHighlight>
      </View>
    );
  }
}

const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
const mapStateToProps = (state) => ({
  gameMatches: state.objects.gameMatches,
  seasons: _.values(state.objects.seasons),
  tournaments: state.objects.tournaments
});
@connect(mapStateToProps, mapDispatchToProps)
@NativeTachyons.wrap
export class MatchCalendar extends PureComponent {

  static propTypes = {
    loadFromServer: PropTypes.func.isRequired,
    seasons: PropTypes.arrayOf(PropTypes.instanceOf(Season)).isRequired,
    tournaments: PropTypes.objectOf(PropTypes.instanceOf(Tournament)).isRequired,
    gameMatches: PropTypes.objectOf(PropTypes.instanceOf(GameMatch)).isRequired,
  }

  state = { refreshing: false, currentSeason: _.last(this.props.seasons) };

  onRefresh = async () => {
    this.setState({ refreshing: true });
    await this.props.loadFromServer();
    this.setState({ refreshing: false });
  }

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
    let { gameMatches, tournaments } = this.props;
    const { currentSeason, refreshing } = this.state;

    gameMatches = _(gameMatches)
      .filter(['seasonId', _.get(currentSeason, 'id', -1)])
      .sortBy(gameMatches, 'time')
      .value();

    return (
      <View cls='flx-i'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/background.png')} />
          <ScrollView cls='flx-i' refreshControl={<RefreshControl refreshing={refreshing} onRefresh={this.onRefresh} tintColor='white' />} >
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
              {_.map(gameMatches, (match) => <MatchInfo key={match.id} match={match} tournament={tournaments[match.tournamentId]} />)}
            </View>
          </ScrollView>
        </View>
        <ScalableImage width={Dimensions.get('window').width} source={require('fc_juarez/assets/img/temp/ad.png')} />
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