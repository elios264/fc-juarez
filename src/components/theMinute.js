import _ from 'lodash';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text, RefreshControl, TouchableHighlight } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { loadFromServer } from 'fc_juarez/src/actions/initializers';
import { Tournament, GameMatch, GameMatchDetails, Advertisement } from 'fc_juarez/src/objects';
import { CacheableImage, CacheableScalableImage } from 'fc_juarez/src/utils';

const icons = {
  '1': require('fc_juarez/assets/img/icons/1.png'),
  '2': require('fc_juarez/assets/img/icons/2.png'),
  '3': require('fc_juarez/assets/img/icons/3.png'),
  '4': require('fc_juarez/assets/img/icons/4.png'),
  '5': require('fc_juarez/assets/img/icons/5.png'),
  '6': require('fc_juarez/assets/img/icons/6.png'),
  '7': require('fc_juarez/assets/img/icons/7.png'),
  '8': require('fc_juarez/assets/img/icons/8.png'),
  '9': require('fc_juarez/assets/img/icons/9.png'),
  '10': require('fc_juarez/assets/img/icons/10.png'),
  '11': require('fc_juarez/assets/img/icons/11.png'),
  '12': require('fc_juarez/assets/img/icons/12.png'),
  '13': require('fc_juarez/assets/img/icons/13.png'),
  '14': require('fc_juarez/assets/img/icons/14.png'),
  '15': require('fc_juarez/assets/img/icons/15.png'),
  '16': require('fc_juarez/assets/img/icons/16.png'),
  '17': require('fc_juarez/assets/img/icons/17.png'),
  '18': require('fc_juarez/assets/img/icons/18.png'),
  '19': require('fc_juarez/assets/img/icons/19.png'),
  '20': require('fc_juarez/assets/img/icons/20.png'),
};


const MatchUpdate = NativeTachyons.wrap(({ minute, desc, image }) => ( // eslint-disable-line react/prop-types
  <View cls='ph3 flx-row aic pv2 bb b--#1d1d1d'>
    <Text cls='contrast ff-ubu'>
      {minute}'
    </Text>
    <Image cls='tint-contrast w3 h2 rm-contain mv1' source={image} />
    <Text cls='flx-i white ff-ubu-m f6'>
      {_.replace(desc, /(<([^>]+)>)/ig, '')}
    </Text>
  </View>
));

const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
const mapStateToProps = (state) => ({
  currentMatch: state.objects.currentMatch,
  tournament: state.objects.tournaments[_.get(state.objects.currentMatch, 'match.tournamentId')],
  ad: state.objects.ads[Advertisement.SmallAd]
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
    ad: PropTypes.instanceOf(Advertisement)
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

    const bravos = { name: 'BRAVOS FC', logo: require('fc_juarez/assets/img/fcjuarez.png') };
    const enemy = { name: _.toUpper(match.versusTeam), logo: { uri: match.teamLogoUrl } };

    const fst = match.versusTeamAtHome ? enemy : bravos;
    const snd = match.versusTeamAtHome ? bravos : enemy;

    return (
      <View>
        <View cls='aic mt3 mb3'>
          <Text cls='ff-ubu-b contrast f6 bg-transparent' >{matchTournament}</Text>
          <Text cls='ff-ubu-b gray f6 bg-transparent' >{matchDate} | {stadium}</Text>
        </View>
        <View cls='flx-row jcc aic h3 mh2' >
          <View cls='flx-i flx-row aic ml2'>
            <CacheableImage cls='w3 h3 rm-stretch' source={fst.logo} />
            <Text cls='flx-i ml2 ff-ubu-b white bg-transparent' style={[styles.smallText]}>{fst.name}</Text>
          </View>
          <Text cls='f4 white ff-ubu-b mh2 bg-transparent'>{match.scoreHome}<Text cls='gray'>  vs  </Text>{match.scoreAway}</Text>
          <View cls='flx-i flx-row aic ml2 jcfe'>
            <Text cls='flx-i ff-ubu-b white bg-transparent tr mr2' style={[styles.smallText]}>{snd.name}</Text>
            <CacheableImage cls='w3 h3 rm-stretch' source={snd.logo} />
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
    const { currentMatch, tournament, ad } = this.props;

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
        <TouchableHighlight onPress={ad ? ad.openTarget : _.noop} >
          <CacheableScalableImage width={Dimensions.get('window').width} source={ ad ? { uri: ad.url } : require('fc_juarez/assets/img/ads/smallAd.png')} />
        </TouchableHighlight>
      </View>
    );
  }
}

const styles = StyleSheet.create({
  smallText: {
    fontSize: sizes.f5 / 1.5
  },
  expand: {
    width: '100%',
    height: '100%'
  }
});