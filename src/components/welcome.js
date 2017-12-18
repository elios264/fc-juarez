import React, { PureComponent } from 'react';
import { StyleSheet, View, Image } from 'react-native';
import NativeTachyons from 'react-native-style-tachyons';


@NativeTachyons.wrap
export class Welcome extends PureComponent {

  render() {

    return (
      <View cls='bg-white flx-i'>
        <Image cls='flx-i rm-stretch' style={[styles.expand]} source={require('fc_juarez/assets/img/temp/welcomebg.png')} />
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