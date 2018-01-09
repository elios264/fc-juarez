import _ from 'lodash';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text, RefreshControl } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { loadFromServer } from 'fc_juarez/src/actions/initializers';
import { Tournament, GameMatch, GameMatchDetails } from 'fc_juarez/src/objects';

const icons = {
  '1': require('fc_juarez/assets/img/icons/cup1.png'),
  '2': require('fc_juarez/assets/img/icons/cup2.png'),
  '3': require('fc_juarez/assets/img/icons/cup1.png'),
  '4': require('fc_juarez/assets/img/icons/cup2.png'),
  '5': require('fc_juarez/assets/img/icons/cup1.png'),
  '6': require('fc_juarez/assets/img/icons/cup2.png'),
  '7': require('fc_juarez/assets/img/icons/field.png'),
  '8': require('fc_juarez/assets/img/icons/yellowCard.png'),
  '9': require('fc_juarez/assets/img/icons/redCard.png'),
  '10': require('fc_juarez/assets/img/icons/flags.png'),
  '11': require('fc_juarez/assets/img/icons/goal.png'),
  '12': require('fc_juarez/assets/img/icons/noIdea.png'),
  '13': require('fc_juarez/assets/img/icons/corner.png'),
  '14': require('fc_juarez/assets/img/icons/ball.png'),
  '15': require('fc_juarez/assets/img/icons/tennis.png'),
  '16': require('fc_juarez/assets/img/icons/yellowCard.png'),
  '17': require('fc_juarez/assets/img/icons/ambulance.png'),
  '18': require('fc_juarez/assets/img/icons/noIdea.png'),
  '19': require('fc_juarez/assets/img/icons/goal.png'),
  '20': require('fc_juarez/assets/img/icons/player1.png'),
};


const MatchUpdate = NativeTachyons.wrap(({ minute, desc, image }) => ( // eslint-disable-line react/prop-types
  <View cls='ph3 flx-row aic pv2 bb b--#1d1d1d'>
    <Text cls='contrast ff-ubu'>
      {minute}'
    </Text>
    <Image cls='tint-contrast w3 h2 rm-contain mv1' source={image} />
    <Text cls='flx-i white ff-ubu-m f6'>
      {desc}
    </Text>
  </View>
));

const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
const mapStateToProps = (state) => ({
  currentMatch: state.objects.currentMatch,
  tournament: state.objects.tournaments[_.get(state.objects.currentMatch, 'match.tournamentId')],
});
@connect(mapStateToProps, mapDispatchToProps)
@NativeTachyons.wrap
export class TheMinute extends PureComponent {

  static propTypes = {
    loadFromServer: PropTypes.func.isRequired,
    tournament: PropTypes.instanceOf(Tournament),
    currentMatch: PropTypes.shape({
      match: PropTypes.instanceOf(GameMatch).isRequired,
      details: PropTypes.arrayOf(PropTypes.instanceOf(GameMatchDetails)).isRequired
    }),
  }

  state = { refreshing: false };

  onRefresh = async () => {
    this.setState({ refreshing: true });
    await this.props.loadFromServer();
    this.setState({ refreshing: false });
  }

  renderDetails() {
    const { currentMatch: { match, details }, tournament } = this.props;

    const stadium = _.toUpper(match.stadium);
    const matchTournament = _.toUpper(tournament.title);
    const matchDate = _.upperCase(match.time.format('DD MMM YYYY'));

    const bravos = { name: 'BRAVOS FC', location: 'CIUDAD JÚAREZ', logo: require('fc_juarez/assets/img/fcjuarez.png') };
    const enemy = { name: _.toUpper(match.versusTeam), location: 'DESCONOCIDA', logo: { uri: match.teamLogoUrl } };

    const fst = match.versusTeamAtHome ? enemy : bravos;
    const snd = match.versusTeamAtHome ? bravos : enemy;

    return (
      <View>
        <View cls='aic mt3 mb3'>
          <Text cls='ff-ubu-b contrast f6 bg-transparent' >{matchTournament}</Text>
          <Text cls='ff-ubu-b gray f6 bg-transparent' >{matchDate} | {stadium}</Text>
        </View>
        <View cls='flx-row jcc aic h3 mh2' >
          <View cls='absolute left-0 flx-row aic ml2'>
            <Image cls='w3 h3 rm-stretch' source={fst.logo} />
            <View cls='ml1' style={[styles.teamInfo]}>
              <Text cls='ff-ubu-b white bg-transparent' style={[styles.smallText]}>{fst.name}</Text>
              <Text cls='ff-ubu-b gray bg-transparent' style={[styles.smallText]}>{fst.location}</Text>
            </View>
          </View>
          <Text cls='ff-ubu-b white f4 bg-transparent'>VS</Text>
          <View cls='absolute right-0 flx-row aic ml2'>
            <View cls='aife mr1' style={[styles.teamInfo]}>
              <Text cls='ff-ubu-b white tr bg-transparent' style={[styles.smallText]}>{snd.name}</Text>
              <Text cls='ff-ubu-b gray bg-transparent' style={[styles.smallText]} >{snd.location}</Text>
            </View>
            <Image cls='w3 h3 rm-stretch' source={snd.logo} />
          </View>
        </View>
        <View cls='mh4 mt4 mb3 bt b--#373737' />
        <View cls='bg-primary ma4 mt0'>
          { details.length
            ? _.map(details, ({ eventId, minute, desc }, idx) => <MatchUpdate key={idx} minute={minute} desc={desc} image={icons[eventId] || icons[1]} />)
            : <MatchUpdate key={0} minute={0} desc='Sin actualizaciones' image={icons[12]} />
          }
        </View>
      </View>
    );
  }

  render() {
    const { currentMatch, tournament } = this.props;

    const contents = currentMatch && tournament
      ? this.renderDetails()
      : <Text cls='white f1 ff-ubu tc mt5' >No hay minuto a minuto.</Text>;

    return (
      <View cls='flx-i'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/background.png')} />
          <ScrollView cls='flx-i' refreshControl={<RefreshControl refreshing={this.state.refreshing} onRefresh={this.onRefresh} tintColor='white' />} >
            {contents}
          </ScrollView>
        </View>
        <ScalableImage width={Dimensions.get('window').width} source={require('fc_juarez/assets/img/temp/ad.png')} />
      </View>
    );
  }
}

const styles = StyleSheet.create({
  teamInfo: {
    maxWidth: sizes.w3 + sizes.w2
  },
  smallText: {
    fontSize: sizes.f5 / 1.5
  },
  expand: {
    width: '100%',
    height: '100%'
  }
});