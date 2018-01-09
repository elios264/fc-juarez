import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text, TouchableHighlight, RefreshControl, Linking } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';
import _ from 'lodash';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { loadFromServer } from 'fc_juarez/src/actions/initializers';
import { GameMatch, Tournament } from 'fc_juarez/src/objects';

const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
const mapStateToProps = (state) => ({
  nextMatch: state.objects.nextMatch,
  tournament: state.objects.tournaments[_.get(state.objects.nextMatch, 'tournamentId')],
});
@connect(mapStateToProps, mapDispatchToProps)
@NativeTachyons.wrap
export class NextMatch extends PureComponent {

  static propTypes = {
    loadFromServer: PropTypes.func.isRequired,
    nextMatch: PropTypes.instanceOf(GameMatch),
    tournament: PropTypes.instanceOf(Tournament),
  }

  state = { refreshing: false };

  onRefresh = async () => {
    this.setState({ refreshing: true });
    await this.props.loadFromServer();
    this.setState({ refreshing: false });
  }

  buyTickets = () => {
    Linking.openURL('https://fcjuarez.boletosenlinea.events/');
  }

  renderNextMatch() {
    const { nextMatch, tournament } = this.props;

    let { time, stadium, versusTeam, versusTeamAtHome, desc, teamLogoUrl, bannerUrl } = nextMatch;

    stadium = _.toUpper(stadium);
    const matchTournament = _.toUpper(tournament.title);
    const matchDate = _.upperCase(time.format('DD MMM YYYY'));

    const bravos = { name: 'BRAVOS FC', location: 'CIUDAD JÚAREZ', logo: require('fc_juarez/assets/img/fcjuarez.png') };
    const enemy = { name: _.toUpper(versusTeam), location: 'DESCONOCIDA', logo: { uri: teamLogoUrl } };

    const fst = versusTeamAtHome ? enemy : bravos;
    const snd = versusTeamAtHome ? bravos : enemy;

    return (
      <View >
        <View cls='bb b--red'>
          <ScalableImage width={Dimensions.get('window').width} source={{ uri: bannerUrl }} />
          <View cls='absolute bottom-0 right-0' style={[styles.triangleCorner]} />
        </View>
        <View cls='aic mt3 mb3'>
          <Text cls='ff-ubu-b contrast f6 bg-transparent' >{matchTournament}</Text>
          <Text cls='ff-ubu-b gray f6 bg-transparent' >{matchDate} | {stadium}</Text>
        </View>
        <View cls='flx-row jcc aic h3 mh2' >
          <View cls='absolute left-0 flx-row aic ml2'>
            <Image cls='w3 h3 rm-stretch' source={fst.logo} />
            <View cls='ml1'>
              <Text cls='ff-ubu-b white bg-transparent' style={[styles.smallText]}>{fst.name}</Text>
              <Text cls='ff-ubu-b gray bg-transparent' style={[styles.smallText]}>{fst.location}</Text>
            </View>
          </View>
          <Text cls='ff-ubu-b white f4 bg-transparent'>VS</Text>
          <View cls='absolute right-0 flx-row aic ml2'>
            <View cls='aife mr1'>
              <Text cls='ff-ubu-b white bg-transparent' style={[styles.smallText]}>{snd.name}</Text>
              <Text cls='ff-ubu-b gray bg-transparent' style={[styles.smallText]} >{snd.location}</Text>
            </View>
            <Image cls='w3 h3 rm-stretch' source={snd.logo} />
          </View>
        </View>
        <View cls='ma4 mb0 bt b--#373737 pt3 pb3'>
          <Text cls='white ff-ubu-b bg-transparent' style={[styles.smallText]}>RESUMEN</Text>
          <Text cls='white ff-ubu-b bg-transparent' style={[styles.smallerText]}>BRAVOS FC <Text cls='gray'>CIUDAD JUÁREZ</Text></Text>
          <Text cls='white ff-ubu mt3 mb3 bg-transparent' style={[styles.smallText]} >
            {desc}
          </Text>
          <TouchableHighlight onPress={this.buyTickets} cls='bg-contrast pv2 jcc aic' underlayColor='#0c963e' >
            <Text cls='#0b6b2e f6 ff-ubu-b'>Comprar boletos</Text>
          </TouchableHighlight>
        </View>
      </View>
    );
  }

  render() {
    const { nextMatch } = this.props;

    const contents = nextMatch
      ? this.renderNextMatch()
      : <Text cls='white f1 ff-ubu tc mt5' >Sin partidos próximos.</Text>;

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
  smallText: {
    fontSize: sizes.f5 / 1.5
  },
  smallerText: {
    fontSize: sizes.f5 / 2
  },
  expand: {
    width: '100%',
    height: '100%'
  },
  triangleCorner: {
    width: 0,
    height: 0,
    backgroundColor: 'transparent',
    borderStyle: 'solid',
    borderRightWidth: sizes.w1 / 2,
    borderTopWidth: sizes.w1 / 2,
    borderRightColor: 'transparent',
    borderTopColor: 'red',
    transform: [ { rotate: '180deg' } ]
  }
});