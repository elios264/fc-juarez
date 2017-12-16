import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Platform, StyleSheet, Text, View, StatusBar } from 'react-native';
import { connect } from 'react-redux';
import Drawer from 'react-native-drawer-menu';

import { Sidebar } from './sideBar';


const instructions = Platform.select({
  ios: 'Press Cmd+R to reload,\n' +
    'Cmd+D or shake for dev menu',
  android: 'Double tap R on your keyboard to reload,\n' +
    'Shake or press menu button for dev menu',
});

const mapStateToProps = (state) => ({ initializing: state.initializing });

@connect(mapStateToProps)
export class App extends PureComponent {

  static propTypes = {
    initializing: PropTypes.bool.isRequired,
  }

  render() {
    const { initializing } = this.props;

    return (
      <Drawer
        drawerWidth={300}
        drawerContent={<Sidebar drawer={{}} />}
      >
        <View style={styles.container}>
          <StatusBar backgroundColor='transparent' translucent={true} barStyle='light-content' />
          <Text style={styles.welcome}>
            Welcome to React Native!
          </Text>
          <Text style={styles.instructions}>
            {initializing && 'Initializing please wait'}
          </Text>
          <Text style={styles.instructions}>
            {instructions}
          </Text>
        </View>
      </Drawer>
    );
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#161616',
  },
  welcome: {
    fontSize: 20,
    textAlign: 'center',
    margin: 10,
    color: 'white'
  },
  instructions: {
    textAlign: 'center',
    color: 'gray',
    marginBottom: 5,
  },
});
