import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, StatusBar } from 'react-native';
import { connect } from 'react-redux';
import Drawer from 'react-native-drawer-menu';
import { Route, Switch } from 'react-router-native';
import OneSignal from 'react-native-onesignal';

import { Sidebar } from './sideBar';
import { Header } from './header';
import { Loader } from './loader';
import { Welcome } from './welcome';
import { Settings } from './settings';
import { NextMatch } from './nextMatch';
import { MatchCalendar } from './matchCalendar';
import { Standings } from './standings';
import { TheMinute } from './theMinute';

const mapStateToProps = (state) => ({ initializing: state.initializing });

@connect(mapStateToProps)
export class App extends PureComponent {

  static propTypes = {
    initializing: PropTypes.bool.isRequired,
  }

  componentWillMount() {
    OneSignal.addEventListener('received', this.onReceived);
    OneSignal.addEventListener('opened', this.onOpened);
    OneSignal.addEventListener('registered', this.onRegistered);
    OneSignal.addEventListener('ids', this.onIds);
  }

  componentWillUnmount() {
    OneSignal.removeEventListener('received', this.onReceived);
    OneSignal.removeEventListener('opened', this.onOpened);
    OneSignal.removeEventListener('registered', this.onRegistered);
    OneSignal.removeEventListener('ids', this.onIds);
  }

  onReceived(notification) {
    console.log('NOTIFICATION RECEIVED: ', notification);
  }

  onOpened(openResult) {
    console.log('NOTIFICATION OPENED: ');
    console.log('Message: ', openResult.notification.payload.body);
    console.log('Data: ', openResult.notification.payload.additionalData);
    console.log('isActive: ', openResult.notification.isAppInFocus);
    console.log('openResult: ', openResult);
  }

  onRegistered(notifData) {
    console.log('Device had been registered for push notifications!', notifData);
  }

  onIds(device) {
    console.log('Device info: ', device);
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
        <StatusBar backgroundColor='transparent' translucent barStyle='light-content' />
        <Switch>
          { initializing && <Route component={Loader} /> }
          <Route render={() => (
            <View style={styles.flex}>
              <Header drawer={this.drawer} />
              <Switch>
                <Route exact path='/' component={Welcome} />
                <Route exact path='/settings' component={Settings} />
                <Route exact path='/next-match' component={NextMatch} />
                <Route exact path='/match-calendar' component={MatchCalendar} />
                <Route exact path='/standings' component={Standings} />
                <Route exact path='/the-minute' component={TheMinute} />
              </Switch>
            </View>
          )} />
        </Switch>
      </Drawer>
    );
  }
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
});