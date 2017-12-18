import React, { PureComponent } from 'react';
import ScalableImage from 'react-native-scalable-image';
import { Text, View, Image, StyleSheet, Dimensions, StatusBar } from 'react-native';
import NativeTachyons from 'react-native-style-tachyons';

@NativeTachyons.wrap
export class Loader extends PureComponent {


  render() {

    return (
      <View cls='absolute-fill jcc aic bg-primarydark'>
        <StatusBar hidden />
        <Image cls='absolute-fill rm-cover' style={[styles.background]} source={require('fc_juarez/assets/img/loader/background.png')} />
        <Image cls='w5 h5 rm-contain' source={require('fc_juarez/assets/img/loader/logoBig.png')} />
        <View cls='aic jcfe' style={[styles.rotate]}>
          <Text cls='white f3 ff-permanent-marker' > FC Juarez <Text cls='contrast'>App </Text></Text>
          <Text cls='gray f5 ff-permanent-marker' > ¡Siéntete bravo! </Text>
        </View>
        <View cls='aic absolute' style={[styles.loader]}>
          <Image cls='w2 h2' source={require('fc_juarez/assets/img/loader/loader.gif')} />
          <Text cls='ff-ubu-b white f6 mt2'>Cargando...</Text>
        </View>
        <ScalableImage cls='absolute right-0 bottom-0' width={Dimensions.get('window').width / 2} source={require('fc_juarez/assets/img/loader/sponsor.png')} />
      </View>
    );
  }
}

const styles = StyleSheet.create({
  background: {
    width: '100%',
    height: '100%'
  },
  rotate: {
    transform: [{ rotate: '-8deg' }]
  },
  loader: {
    bottom: '10%'
  }
});