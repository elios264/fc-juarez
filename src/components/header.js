import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Text, View, Image, TouchableOpacity, StyleSheet } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';

import { palette } from 'fc_juarez/src/theme';

@NativeTachyons.wrap
export class Header extends PureComponent {

  static propTypes = {
    drawer: PropTypes.object,
  }

  openDrawer = () => {
    this.props.drawer.openDrawer();
  }

  render() {

    return (
      <View cls='bg-#111111 pb3 pt4 bb b--contrast' style={[styles.container]} >
        <View cls='aic jcc flx-row'>
          <TouchableOpacity cls='absolute left-0 jcc w3 h3' onPress={this.openDrawer} >
            <Image cls='ml3 rm-contain w1' source={require('fc_juarez/assets/img/header/menu.png')} />
          </TouchableOpacity>
          <View cls='w3'>
            <Image style={[styles.expand]} cls='rm-contain' source={require('fc_juarez/assets/img/header/logo1.png')} />
          </View>
          <View cls='aic jcfe' style={[styles.rotate]}>
            <Text cls='white f4 ff-permanent-marker bg-transparent' > FC Juarez <Text cls='contrast'>App </Text></Text>
            <Text cls='gray f6 ff-permanent-marker bg-transparent' > ¡Siéntete bravo! </Text>
          </View>
          <View cls='w2'>
            <Image style={[styles.expand]} cls='rm-contain' source={require('fc_juarez/assets/img/header/logo2.png')} />
          </View>
        </View>
        <View cls='absolute bottom-0 right-0' style={[styles.triangleCorner]} />
      </View>
    );
  }
}

const styles = StyleSheet.create({
  container: {
    height: sizes.h2 + sizes.h3
  },
  expand: {
    width: '100%',
    height: '100%'
  },
  rotate: {
    transform: [{ rotate: '-8deg' }]
  },
  triangleCorner: {
    width: 0,
    height: 0,
    backgroundColor: 'transparent',
    borderStyle: 'solid',
    borderRightWidth: sizes.w1 / 1.5,
    borderTopWidth: sizes.w1 / 1.5,
    borderRightColor: 'transparent',
    borderTopColor: palette.contrast,
    transform: [ { rotate: '180deg' } ]
  }
});