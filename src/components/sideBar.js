import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Text, View, Image, TouchableOpacity } from 'react-native';
import NativeTachyons, { sizes } from 'react-native-style-tachyons';
import { Link, withRouter } from 'react-router-native';

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

  render() {

    return (
      <View cls='flx-i bg-primarydark'>
        <View cls='bg-primary aic jcc' style={{ height: sizes.h2 + sizes.h3 }}>
          <TouchableOpacity cls='absolute left-1' onPress={this.closeDrawer}>
            <Image cls='h1 w1' source={require('fc_juarez/assets/img/back.png')} />
          </TouchableOpacity>
          <Text cls='white f3 ff-permanent-marker'> FC Juarez <Text cls='contrast'>App </Text></Text>
          <Text cls='gray ff-ubu-b absolute' style={{ fontSize: sizes.f5 / 2, bottom: sizes.mb1 + sizes.mb3 }}>
            FC JÚAREZ | V1.0
          </Text>
        </View>
        <View cls='flx-i'>
          <MenuElement path='/' image={require('fc_juarez/assets/img/menu/ball.png')} text='Próximo partido' />
          <MenuElement path='/' image={require('fc_juarez/assets/img/menu/score.png')} text='Tabla general' />
          <MenuElement path='/' image={require('fc_juarez/assets/img/menu/tv.png')} text='Calendario de partidos' />
          <MenuElement path='/' image={require('fc_juarez/assets/img/menu/whistle.png')} text='Minuto a minuto' />
          <MenuElement path='/settings' image={require('fc_juarez/assets/img/menu/timer.png')} text='Configuración' />

          <Text cls='gray ff-ubu-b absolute bottom-1 left-1' style={{ fontSize: sizes.f5 / 2 }}>
            Todos los derechos reservados 2017
          </Text>
        </View>
      </View>
    );
  }
}