import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';

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

@NativeTachyons.wrap
export class TheMinute extends PureComponent {

  render() {

    return (
      <View cls='flx-i'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/background.png')} />
          <ScrollView cls='flx-i' alwaysBounceVertical={false} bounces={false}>
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
            <View cls='mh4 mt4 mb3 bt b--#373737' />
            <View cls='bg-primary ma4 mt0'>
              <MatchUpdate minute={36} desc='Amonestación Alebrijes - Orlando Pineda' image={require('fc_juarez/assets/img/icons/yellowCard.png')} />
              <MatchUpdate minute={58} desc='Cambio de FC Juárez sale Willian Antunes entra Raúl Enríquez' image={require('fc_juarez/assets/img/icons/player2.png')} />
              <MatchUpdate minute={63} desc='Cambio FC Juárez sale Mauro Fernández entra Irving Avalos' image={require('fc_juarez/assets/img/icons/player2.png')} />
              <MatchUpdate minute={65} desc='Amonestación Alebrijes - Taufic Guarch ' image={require('fc_juarez/assets/img/icons/yellowCard.png')} />
              <MatchUpdate minute={67} desc='Amonestación Alebrijes - Madrigal' image={require('fc_juarez/assets/img/icons/yellowCard.png')} />
              <MatchUpdate minute={73} desc='Cambio de FC Juárez sale Jonathan Lacerda entra Rodrigo Prieto' image={require('fc_juarez/assets/img/icons/player2.png')} />
              <MatchUpdate minute={73} desc='Amonestación FC Juárez – Alejandro Berber' image={require('fc_juarez/assets/img/icons/yellowCard.png')} />
              <MatchUpdate minute={74} desc='Cambio de Alebrijes sale Carlos Gael entra David Álvarez' image={require('fc_juarez/assets/img/icons/player2.png')} />
              <MatchUpdate minute={77} desc='Expulsión Alebrijes - Juan Manuel Rivera' image={require('fc_juarez/assets/img/icons/redCard.png')} />
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
  expand: {
    width: '100%',
    height: '100%'
  }
});