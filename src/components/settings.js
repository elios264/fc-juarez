import PropTypes from 'prop-types';

import React, { PureComponent, Fragment } from 'react';
import { StyleSheet, View, Image, Text, Switch, Dimensions } from 'react-native';
import { bindActionCreators } from 'redux';
import NativeTachyons from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';
import { connect } from 'react-redux';

import { palette } from 'fc_juarez/src/theme';
import { updatePushSettings } from 'fc_juarez/src/actions/initializers';


const mapDispatchToProps = (dispatch) => bindActionCreators({ updatePushSettings }, dispatch);
const mapStateToProps = (state) => ({
  notificationsAllowed: state.appInfo.pushPermissions.notificationsEnabled,
  pushSettings: state.pushSettings,
});

@connect(mapStateToProps, mapDispatchToProps)
@NativeTachyons.wrap
export class Settings extends PureComponent {

  static propTypes = {
    notificationsAllowed: PropTypes.bool,
    updatePushSettings: PropTypes.func.isRequired,
    pushSettings: PropTypes.object.isRequired,
  }

  render() {
    const { pushSettings, updatePushSettings, notificationsAllowed } = this.props;
    const { receiveMatchAlerts, receiveGoalsAlerts, receiveGeneralAlerts } = pushSettings;

    return (
      <View cls='flx-i bg-white'>
        <View cls='flx-i bg-primary'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/settingsbg.png')} />
          <ScalableImage cls='absolute bottom-0 left-0' width={Dimensions.get('window').width} source={require('fc_juarez/assets/img/green-bar.png')} />

         { !notificationsAllowed &&
            <View cls='flx-row aic mt4 ml4 mr3'>
              <Text cls='flx-i yellow ff-ubu-b bg-transparent'>Notificaciones desactivadas, ve a preferencias del sistema y activalas.</Text>
            </View>
          }
         { notificationsAllowed && 
          <Fragment>
            <View cls='flx-row aic mt4 ml4 mr3'>
              <Text cls='flx-i white ff-ubu-b bg-transparent'>Activar alerta de partidos</Text>
              <Switch value={receiveMatchAlerts} onValueChange={updatePushSettings.bind(null, 'receiveMatchAlerts')} onTintColor={palette.contrast} thumbTintColor='white' tintColor={palette.gray}/>
            </View>
            <View cls='flx-row aic mt4 ml4 mr3'>
              <Text cls='flx-i white ff-ubu-b bg-transparent'>Activar alerta de goles</Text>
              <Switch value={receiveGoalsAlerts} onValueChange={updatePushSettings.bind(null, 'receiveGoalsAlerts')} onTintColor={palette.contrast} thumbTintColor='white' tintColor={palette.gray}/>
            </View>
            <View cls='flx-row aic mt4 ml4 mr3'>
              <Text cls='flx-i white ff-ubu-b bg-transparent'>Activar alertas generales</Text>
              <Switch value={receiveGeneralAlerts} onValueChange={updatePushSettings.bind(null, 'receiveGeneralAlerts')} onTintColor={palette.contrast} thumbTintColor='white' tintColor={palette.gray}/>
            </View>
            <Text cls='white f5 ff-ubu' >{JSON.stringify(this.props.pushPermissions)}</Text>
          </Fragment>
         }
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