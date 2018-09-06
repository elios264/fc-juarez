import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Text, View, Image, TouchableOpacity, StyleSheet, Linking } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import { Link, withRouter } from 'react-router-native';

import { ifIphoneX } from 'fcjuarez/src/utils';


const MenuElement = NativeTachyons.wrap(({ image, text, path }) => (
  <Link to={path} component={TouchableOpacity} >
    <View cls='h3 b--#171717 bt flx-row'>
      <View cls='w3 aife jcc'>
        <Image cls='w2 h2 rm-contain' source={image} />
      </View>
      <Text cls='white ff-ubu-b f5 ml3 asc'>{text}</Text>
    </View>
  </Link>
));
MenuElement.propTypes = {
  image: PropTypes.number.isRequired,
  text: PropTypes.string.isRequired,
  path: PropTypes.string.isRequired,
};

export class _Sidebar extends PureComponent {

  static propTypes = {
    drawer: PropTypes.object.isRequired,
  }

  componentDidUpdate(prevProps) {
    if (this.props.location !== prevProps.location) {
      this.closeDrawer();
    }
  }

  closeDrawer = () => {
    this.props.drawer.closeDrawer();
  }

  openTranstelco = () => {
    Linking.openURL('https://transtelco.net/');
  }

  render() {

    return (
      <View cls='flx-i bg-primarydark'>
        <View cls='bg-primary aic jcc' style={[styles.header]}>
          <TouchableOpacity cls='absolute left-1 bottom-1 jcc aic w3 h3' onPress={this.closeDrawer}>
            <Image cls='h1 w1' source={require('fcjuarez/assets/img/back.png')} />
          </TouchableOpacity>
          <Link to='/' component={TouchableOpacity} >
            <Image cls='rm-contain w4' resizeMethod='resize' source={require('fcjuarez/assets/img/header/logo-bravos.png')} />
          </Link>
          <Text cls='gray ff-ubu-b absolute bottom-1' style={{ fontSize: sizes.f5 / 2, left: 140 }}>
            FC JÚAREZ | V1.0
          </Text>
        </View>
        <View cls='flx-i'>
          <MenuElement path='/next-match' image={require('fcjuarez/assets/img/menu/ball.png')} text='Próximo partido' />
          <MenuElement path='/standings' image={require('fcjuarez/assets/img/menu/score.png')} text='Tabla general' />
          <MenuElement path='/match-calendar' image={require('fcjuarez/assets/img/menu/tv.png')} text='Calendario de partidos' />
          <MenuElement path='/the-minute' image={require('fcjuarez/assets/img/menu/whistle.png')} text='Minuto a minuto' />
          <MenuElement path='/settings' image={require('fcjuarez/assets/img/menu/timer.png')} text='Configuración' />

          <View cls='flx-i flx-row aic jcc' >
            <TouchableOpacity onPress={this.openTranstelco} >
              <Image cls='rm-contain' style={[styles.logo]} source={require('fcjuarez/assets/img/menu/transtelco.png')} />
            </TouchableOpacity>
          </View>

          <Text cls='mb3 ml3 gray ff-ubu-b' style={{ fontSize: sizes.f5 / 2 }}>
              Todos los derechos reservados 2018
          </Text>
        </View>
      </View>
    );
  }
}


const styles = StyleSheet.create({
  header: {
    ...ifIphoneX({
      height: sizes.h3 + sizes.h3,
      paddingTop: sizes.mt4
    }, {
      height: sizes.h2 + sizes.h3
    })
  },
  expand: {
    width: '100%',
    height: '100%'
  },
  logo: {
    width: sizes.w5 + sizes.w2,
    height: sizes.h3 + sizes.h1
  }
});

export const Sidebar = withRouter(NativeTachyons.wrap(_Sidebar));