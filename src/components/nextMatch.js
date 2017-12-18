import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';

@NativeTachyons.wrap
export class NextMatch extends PureComponent {

  render() {

    return (
      <View cls='flx-i'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/background.png')} />
          <ScrollView cls='flx-i'>
            <View cls='bb b--red'>
              <ScalableImage width={Dimensions.get('window').width} source={require('fc_juarez/assets/img/temp/nextMatchImg.png')} />
              <View cls='absolute bottom-0 right-0' style={[styles.triangleCorner]} />
            </View>
            <View cls='aic mt3 mb3'>
              <Text cls='ff-ubu-b contrast f6' >LIGA DE ASCENSO</Text>
              <Text cls='ff-ubu-b gray f6' >03 MAR 2017 | ESTADIO JALISCO</Text>
            </View>
            <View cls='flx-row jcc aic h3 mh2' >
              <View cls='absolute left-0 flx-row aic ml2'>
                <Image cls='w3 h3 rm-stretch' source={require('fc_juarez/assets/img/teams/fcjuarez.png')} />
                <View cls='ml1'>
                  <Text cls='ff-ubu-b white' style={[styles.smallText]}>BRAVOS FC</Text>
                  <Text cls='ff-ubu-b gray' style={[styles.smallText]}>CIUDAD JÚAREZ</Text>
                </View>
              </View>
              <Text cls='ff-ubu-b white f4'>VS</Text>
              <View cls='absolute right-0 flx-row aic ml2'>
                <View cls='aife mr1'>
                  <Text cls='ff-ubu-b white' style={[styles.smallText]}>DORADOS</Text>
                  <Text cls='ff-ubu-b gray' style={[styles.smallText]} >SINALOA</Text>
                </View>
                <Image cls='w3 h3 rm-stretch' source={require('fc_juarez/assets/img/teams/doradosdesinaloa.png')} />
              </View>
            </View>
            <View cls='ma4 mb0 bt b--#373737 pt3'>
              <Text cls='white ff-ubu-b' style={[styles.smallText]}>RESUMEN</Text>
              <Text cls='white ff-ubu-b' style={[styles.smallerText]}>BRAVOS FC <Text cls='gray'>CIUDAD JUÁREZ</Text></Text>
              <Text cls='white ff-ubu mt3' style={[styles.smallText]} >
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                {'\n'}{'\n'}
                Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
              </Text>
            </View>
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