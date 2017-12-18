import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Platform, StyleSheet, Text, View, StatusBar } from 'react-native';
import { connect } from 'react-redux';
import Drawer from 'react-native-drawer-menu';
import NativeTachyons from 'react-native-style-tachyons';
import { Route, Switch } from 'react-router-native';

import { Sidebar } from './sideBar';
import { Header } from './header';
import { Loader } from './loader';

const instructions = Platform.select({
  ios: 'Press Cmd+R to reload,\n' +
    'Cmd+D or shake for dev menu',
  android: 'Double tap R on your keyboard to reload,\n' +
    'Shake or press menu button for dev menu',
});

const mapStateToProps = (state) => ({ initializing: state.initializing });

@connect(mapStateToProps)
@NativeTachyons.wrap
export class App extends PureComponent {

  static propTypes = {
    initializing: PropTypes.bool.isRequired,
  }

  setDrawerRef = (ref) => this.drawer = ref;

  render() {
    const { initializing } = this.props;

    return (
      <Drawer
        ref={this.setDrawerRef}
        disabled={initializing}
        drawerWidth={300}
        drawerContent={<Sidebar drawer={{}} />}
      >
        <StatusBar backgroundColor='transparent' translucent={true} barStyle='light-content' />
        <Switch>
          { initializing && <Route component={Loader} /> }
          <Route render={() => (
            <View cls='flx-i'>
              <Header drawer={this.drawer} />
              <View cls='flx-i jcc aic bg-#161616'>
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
            </View>
          )} />
        </Switch>



      </Drawer>
    );
  }
}

const styles = StyleSheet.create({

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
