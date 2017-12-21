import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text, TouchableOpacity, TouchableHighlight } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';
import _ from 'lodash';

const MatchInfo = NativeTachyons.wrap(({ team1, team2, image1, image2, goals1, goals2, date, time, place, cat }) => ( // eslint-disable-line react/prop-types
  <View cls='aic mv3'>
    <View cls='flx-row aic jcc mb2'>
      <Text cls='white ff-ubu-b mr2 f6 tr flx-i bg-transparent'>{team1}</Text>
      <Image cls='pa1 rm-contain' style={[styles.logo]} source={image1} />
      <Text cls='white ff-ubu-b mh2 bg-transparent'>{goals1}<Text cls='gray'> vs </Text>{goals2}</Text>
      <Image cls='pa1 rm-contain' style={[styles.logo]} source={image2} />
      <Text cls='white ff-ubu-b ml2 f6 tl flx-i bg-transparent'>{team2}</Text>
    </View>
    <Text cls='white ff-ubu-b mb1 bg-transparent'>{date}<Text cls='gray'>  |  </Text>{time}</Text>
    <Text cls='contrast ff-ubu-b mb3 bg-transparent'>{place}<Text cls='gray'>  |  </Text>{cat}</Text>
    <TouchableHighlight onPress={_.noop} cls='bg-contrast pv2 jcc aic ass' underlayColor='#0c963e' >
      <Text cls='white f6 ff-ubu-b bg-transparent'>Ver más</Text>
    </TouchableHighlight>
  </View>
));


@NativeTachyons.wrap
export class MatchCalendar extends PureComponent {

  openPicker = () => {
  }


  render() {

    return (
      <View cls='flx-i'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/background.png')} />
          <ScrollView cls='flx-i'>
            <View cls='aic mv3 mh2 flx-row jcsb'>
              <Text cls='f5 mr3 ff-ubu-m white bg-transparent'>Calendario <Text cls='#AAAAAA'>de partidos</Text></Text>
              <TouchableOpacity cls='flx-i jcc bg-rgba(13,13,13,0.8)' onPress={this.openPicker} activeOpacity={0.8} >
                <Text cls='ff-ubu-b white f6 ma2'>Ver apertura 2017</Text>
                <View cls='absolute right-1' style={[styles.triangle]} />
              </TouchableOpacity>
            </View>
            <View cls='bt b--#373737' />
            <View cls='mh2 mv3'>
              <Text cls='mb2 white ff-ubu-b bg-transparent'>Apertura 2017</Text>
              <MatchInfo
                team1='Celaya F.C.'
                team2='FC Juárez'
                goals1={1}
                goals2={1}
                image1={require('fc_juarez/assets/img/teams/celayafc.png')}
                image2={require('fc_juarez/assets/img/teams/fcjuarez.png')}
                date='Jul/22/2017'
                time='07:00 PM'
                place='Olímpico Benito Juárez'
                cat='Ascenso'
              />
              <MatchInfo
                team1='Chivas'
                team2='FC Juárez'
                goals1={2}
                goals2={1}
                image1={require('fc_juarez/assets/img/teams/udeg.png')}
                image2={require('fc_juarez/assets/img/teams/fcjuarez.png')}
                date='Ago/22/2017'
                time='07:00 PM'
                place='Estadio Chivas'
                cat='Copa Mx'
              />
              <MatchInfo
                team1='Celaya F.C.'
                team2='FC Juárez'
                goals1={1}
                goals2={1}
                image1={require('fc_juarez/assets/img/teams/celayafc.png')}
                image2={require('fc_juarez/assets/img/teams/fcjuarez.png')}
                date='Jul/22/2017'
                time='07:00 PM'
                place='Olímpico Benito Juárez'
                cat='Ascenso'
              />
              <MatchInfo
                team1='Chivas'
                team2='FC Juárez'
                goals1={2}
                goals2={1}
                image1={require('fc_juarez/assets/img/teams/udeg.png')}
                image2={require('fc_juarez/assets/img/teams/fcjuarez.png')}
                date='Ago/22/2017'
                time='07:00 PM'
                place='Estadio Chivas'
                cat='Copa Mx'
              />
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