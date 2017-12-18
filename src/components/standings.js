import React, { PureComponent } from 'react';
import { StyleSheet, View, Dimensions, Image, ScrollView, Text } from 'react-native';
import NativeTachyons from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';

@NativeTachyons.wrap
export class Standings extends PureComponent {

  render() {

    return (
      <View cls='flx-i'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/background.png')} />
          <ScrollView cls='flx-i'>
            <Text cls='f1 white'>Tabla general</Text>
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
});