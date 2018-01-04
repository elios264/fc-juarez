import React, { PureComponent } from 'react';
import { StyleSheet, View, Image, Text, Switch, Dimensions } from 'react-native';
import NativeTachyons from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';

import { palette } from 'fc_juarez/src/theme';

@NativeTachyons.wrap
export class Settings extends PureComponent {

  render() {

    return (
      <View cls='flx-i bg-white'>
        <View cls='flx-i bg-primary'>

          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/temp/settingsbg.png')} />
          <ScalableImage cls='absolute bottom-0 left-0' width={Dimensions.get('window').width} source={require('fc_juarez/assets/img/green-bar.png')} />

          <View cls='flx-row aic mt4 ml4 mr3'>
            <Text cls='flx-i white ff-ubu-b bg-transparent'>Activar alerta de partidos</Text>
            <Switch value onTintColor={palette.contrast} thumbTintColor='white' tintColor={palette.gray}/>
          </View>
          <View cls='flx-row aic mt4 ml4 mr3'>
            <Text cls='flx-i white ff-ubu-b bg-transparent'>Activar alerta de goles</Text>
            <Switch value onTintColor={palette.contrast} thumbTintColor='white' tintColor={palette.gray}/>
          </View>
          <View cls='flx-row aic mt4 ml4 mr3'>
            <Text cls='flx-i white ff-ubu-b bg-transparent'>Activar alertas generales</Text>
            <Switch onTintColor={palette.contrast} thumbTintColor='white' tintColor={palette.gray}/>
          </View>
        </View>
        <View cls='h4 pa2'>
          <Image style={[styles.expand]} source={require('fc_juarez/assets/img/temp/welcomead.png')} />
        </View>
      </View>
    );
  }
}

const styles = StyleSheet.create({
  expand: {
    width: '100%',
    height: '100%'
  },
});