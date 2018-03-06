import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Text, View, Image, TouchableOpacity, StyleSheet, Linking } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import { Link, withRouter } from 'react-router-native';

import { ifIphoneX } from 'fc_juarez/src/utils';


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

@withRouter
@NativeTachyons.wrap
export class Sidebar extends PureComponent {

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

  openEuphos = () => {
    Linking.openURL('https://www.eupho.io/');
  }

  render() {

    return (
      <View cls='flx-i bg-primarydark'>
        <View cls='bg-primary aic jcc' style={[styles.header]}>
          <TouchableOpacity cls='absolute left-1 jcc aic w3 h3' onPress={this.closeDrawer}>
            <Image cls='h1 w1' source={require('fc_juarez/assets/img/back.png')} />
          </TouchableOpacity>
          <Link to='/' component={TouchableOpacity} >
            <Image cls='rm-contain w4' resizeMethod='resize' source={require('fc_juarez/assets/img/header/logo-bravos.png')} />
          </Link>
          <Text cls='gray ff-ubu-b absolute bottom-1' style={{ fontSize: sizes.f5 / 2, left: 140 }}>
            FC JÚAREZ | V1.0
          </Text>
        </View>
        <View cls='flx-i'>
          <MenuElement path='/next-match' image={require('fc_juarez/assets/img/menu/ball.png')} text='Próximo partido' />
          <MenuElement path='/standings' image={require('fc_juarez/assets/img/menu/score.png')} text='Tabla general' />
          <MenuElement path='/match-calendar' image={require('fc_juarez/assets/img/menu/tv.png')} text='Calendario de partidos' />
          <MenuElement path='/the-minute' image={require('fc_juarez/assets/img/menu/whistle.png')} text='Minuto a minuto' />
          <MenuElement path='/settings' image={require('fc_juarez/assets/img/menu/timer.png')} text='Configuración' />

          <View cls='flx-i jcc' >
            <View cls='flx-row' >
              <TouchableOpacity onPress={this.openEuphos} >
                <View cls='aic ml4' >
                  <Image cls='mb1 rm-contain w4' source={require('fc_juarez/assets/img/menu/eupho.png')} />
                  <Text cls='white ff-ubu-b f5 asc'>Ve los partidos en vivo</Text>
                </View>
              </TouchableOpacity>
            </View>
          </View>

          <Text cls=' mb3 ml3 gray ff-ubu-b' style={{ fontSize: sizes.f5 / 2 }}>
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
      height: sizes.h3 + sizes.h3
    }, {
      height: sizes.h2 + sizes.h3
    })
  },
  expand: {
    width: '100%',
    height: '100%'
  },
});