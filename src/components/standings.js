import _ from 'lodash';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text, RefreshControl, TouchableHighlight } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import { loadFromServer } from 'fc_juarez/src/actions/initializers';
import { TeamInfo, Advertisement } from 'fc_juarez/src/objects';

const TeamHeader = NativeTachyons.wrap(({ image, name }) => ( // eslint-disable-line react/prop-types
  <View cls='flx-row aic mv1 mr2 h3'>
    <Image source={image} cls='rm-contain' style={[styles.logoSize]} />
    <Text cls='white ff-ubu-b f6 ml2 tc flx-i bg-transparent'>{name}</Text>
  </View>
));

const Score = NativeTachyons.wrap(({ score }) => ( // eslint-disable-line react/prop-types
  <View cls='h3 jcc aife ml1 mr2 mv1'>
    <Text cls='f6 ff-ubu-b white bg-transparent'>{score}</Text>
  </View>
));

const mapStateToProps = (state) => ({ teamsInfo: state.objects.teamsInfo, ad: state.objects.ads[Advertisement.SmallAd] });
const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
@connect(mapStateToProps, mapDispatchToProps)
@NativeTachyons.wrap
export class Standings extends PureComponent {

  static propTypes = {
    loadFromServer: PropTypes.func.isRequired,
    teamsInfo: PropTypes.objectOf(PropTypes.instanceOf(TeamInfo)).isRequired,
    ad: PropTypes.instanceOf(Advertisement)
  }

  state = { refreshing: false };

  onRefresh = async () => {
    this.setState({ refreshing: true });
    await this.props.loadFromServer();
    this.setState({ refreshing: false });
  }

  render() {
    let { teamsInfo, ad } = this.props;

    teamsInfo = _.orderBy(teamsInfo, 'name');

    return (
      <View cls='flx-i'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/background.png')} />
          <ScrollView cls='flx-i' refreshControl={<RefreshControl refreshing={this.state.refreshing} onRefresh={this.onRefresh} tintColor='white' />} >
            <Text cls='mv3 ml3 f3 ff-ubu-m white bg-transparent'>Tabla <Text cls='#AAAAAA'>general</Text></Text>
            <View cls='bt b--#373737' />
            <View cls='mt3 ml2 mr1 flx-row'>
              <View cls='flx-i' style={[styles.maxDesc]}>
                <View cls='h2 jcc'>
                  <Text cls='white ff-ubu bg-transparent'>Liga de ascenso</Text>
                </View>
                { _.map(teamsInfo, ({ name, logoUrl, id }) => <TeamHeader key={id} image={{ uri: logoUrl }} name={name} />)}
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>JJ</Text>
                </View>
                { _.map(teamsInfo, ({ jj, id }) => <Score key={id} score={jj} />)}
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>JG</Text>
                </View>
                { _.map(teamsInfo, ({ jg, id }) => <Score key={id} score={jg} />)}
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>JE</Text>
                </View>
                { _.map(teamsInfo, ({ je, id }) => <Score key={id} score={je} />)}
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>JP</Text>
                </View>
                { _.map(teamsInfo, ({ jp, id }) => <Score key={id} score={jp} />)}
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>GF</Text>
                </View>
                { _.map(teamsInfo, ({ gf, id }) => <Score key={id} score={gf} />)}
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>GC</Text>
                </View>
                { _.map(teamsInfo, ({ gc, id }) => <Score key={id} score={gc} />)}
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>DIF</Text>
                </View>
                { _.map(teamsInfo, ({ dif, id }) => <Score key={id} score={dif} />)}
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>PTS</Text>
                </View>
                { _.map(teamsInfo, ({ pts, id }) => <Score key={id} score={pts} />)}
              </View>

            </View>
          </ScrollView>
        </View>
        <TouchableHighlight onPress={ad ? ad.openTarget : _.noop} >
          <ScalableImage width={Dimensions.get('window').width} source={ ad ? { uri: ad.url } : require('fc_juarez/assets/img/ads/smallAd.png')} />
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
  logoSize: {
    width: sizes.w2 + sizes.w1,
    height: sizes.h1 + sizes.h2
  },
  maxDesc: {
    maxWidth: sizes.w4 + sizes.w2
  }
});