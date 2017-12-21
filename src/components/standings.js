import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text, RefreshControl } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';

const TeamInfo = NativeTachyons.wrap(({ image, name }) => ( // eslint-disable-line react/prop-types
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

@NativeTachyons.wrap
export class Standings extends PureComponent {

  state = { refreshing: false };

  onRefresh = async () => {
    this.setState({ refreshing: true });
    await new Promise((res) => setTimeout(res, 2000));
    this.setState({ refreshing: false });
  }

  render() {

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
                <TeamInfo image={require('fc_juarez/assets/img/teams/celayafc.png')} name='Celaya F.C.' />
                <TeamInfo image={require('fc_juarez/assets/img/teams/fcjuarez.png')} name='FC Juárez' />
                <TeamInfo image={require('fc_juarez/assets/img/teams/tmfutbolclub.png')} name='TM Futbol Club' />
                <TeamInfo image={require('fc_juarez/assets/img/teams/clubatleticozacatepec.png')} name='Atlético Zacatepec' />
                <TeamInfo image={require('fc_juarez/assets/img/teams/alebrijes.png')} name='Alebrijes' />
                <TeamInfo image={require('fc_juarez/assets/img/teams/mineros.png')} name='Mineros' />
                <TeamInfo image={require('fc_juarez/assets/img/teams/cafetaleros.png')} name='Cafetaleros' />
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>JJ</Text>
                </View>
                <Score score={15} />
                <Score score={15} />
                <Score score={15} />
                <Score score={15} />
                <Score score={15} />
                <Score score={15} />
                <Score score={15} />
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>JG</Text>
                </View>
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>JE</Text>
                </View>
                <Score score={4} />
                <Score score={4} />
                <Score score={4} />
                <Score score={4} />
                <Score score={4} />
                <Score score={4} />
                <Score score={4} />
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>JP</Text>
                </View>
                <Score score={3} />
                <Score score={3} />
                <Score score={3} />
                <Score score={3} />
                <Score score={3} />
                <Score score={3} />
                <Score score={3} />
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>GF</Text>
                </View>
                <Score score={17} />
                <Score score={17} />
                <Score score={17} />
                <Score score={17} />
                <Score score={17} />
                <Score score={17} />
                <Score score={17} />
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>GC</Text>
                </View>
                <Score score={9} />
                <Score score={9} />
                <Score score={9} />
                <Score score={9} />
                <Score score={9} />
                <Score score={9} />
                <Score score={9} />
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>DIF</Text>
                </View>
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
                <Score score={8} />
              </View>
              <View>
                <View cls='h2 jcc'>
                  <Text cls='contrast ff-ubu-b ml1 mr2 f6 bg-transparent'>PTS</Text>
                </View>
                <Score score={28} />
                <Score score={28} />
                <Score score={28} />
                <Score score={28} />
                <Score score={28} />
                <Score score={28} />
                <Score score={128} />
              </View>

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
  logoSize: {
    width: sizes.w2 + sizes.w1,
    height: sizes.h1 + sizes.h2
  },
  maxDesc: {
    maxWidth: sizes.w4 + sizes.w2
  }
});