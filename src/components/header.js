import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Text, View, Image, TouchableOpacity, StyleSheet } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';

import { palette } from 'fc_juarez/src/theme';
import { ifIphoneX } from 'fc_juarez/src/utils';

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
      <View cls='bg-#111111 pb3 bb b--contrast' style={[styles.container]} >
        <View cls='aic jcc flx-row'>
          <TouchableOpacity cls='absolute left-0 jcc w4 h3' onPress={this.openDrawer} >
            <Image cls='ml4 rm-contain' style={[styles.w1dot5]} source={require('fc_juarez/assets/img/header/menu.png')} />
          </TouchableOpacity>
          <View cls='w4'>
            <Image style={[styles.expand]} cls='rm-contain' resizeMethod='resize' source={require('fc_juarez/assets/img/header/logo-bravos.png')} />
          </View>
        </View>
        <View cls='absolute bottom-0 right-0' style={[styles.triangleCorner]} />
      </View>
    );
  }
}

const styles = StyleSheet.create({
  container: {
    ...ifIphoneX({
      paddingTop: sizes.pt4 + sizes.pt3,
      height: sizes.h3 + sizes.h3
    }, {
      paddingTop: sizes.pt4,
      height: sizes.h2 + sizes.h3
    })
  },
  w1dot5: {
    width: sizes.w1 + (sizes.w1 / 2)
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
    borderRightWidth: sizes.w1 / 1.5,
    borderTopWidth: sizes.w1 / 1.5,
    borderRightColor: 'transparent',
    borderTopColor: palette.contrast,
    transform: [ { rotate: '180deg' } ]
  }
});