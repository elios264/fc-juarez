import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, StatusBar } from 'react-native';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import Drawer from 'react-native-drawer-menu';
import { Route, Switch } from 'react-router-native';
import OneSignal from 'react-native-onesignal';
import _ from 'lodash';

import { loadFromServer } from 'fcjuarez/src/actions/initializers';
import { Sidebar } from './sideBar';
import { Header } from './header';
import { Welcome } from './welcome';
import { Settings } from './settings';
import { NextMatch } from './nextMatch';
import { MatchCalendar } from './matchCalendar';
import { Standings } from './standings';
import { TheMinute } from './theMinute';

let coldStartNotification = undefined;
OneSignal.addEventListener('opened', (openResult) => {
  coldStartNotification = openResult;
});

const mapStateToProps = (state) => ({ initializing: state.initializing });
const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
const responderNegotiate = (e, { dx }) => (dx >= 20 || dx <= -20);
class _App extends PureComponent {

  static propTypes = {
    initializing: PropTypes.bool.isRequired,
    loadFromServer: PropTypes.func.isRequired,
  }

  componentDidMount() {    
    if (coldStartNotification) {
      this.onOpened(coldStartNotification, false);
      coldStartNotification = undefined;
    }
    OneSignal.addEventListener('opened', this.onOpened);
  }


  onOpened = (openResult, reload = true) => {
    const { history, loadFromServer } = this.props;
    const pageToNavigate = _.get(openResult, 'notification.payload.additionalData.page');
    switch (pageToNavigate) {
      case 'minute': history.push('/the-minute'); break;
      case 'matchCalendar': history.push('/next-match'); break;
      default: break;
    }
    if (reload) {
      loadFromServer();
    }
  }

  setDrawerRef = (ref) => this.drawer = ref;

  render() {
    const { initializing } = this.props;

    return (
      <Drawer
        ref={this.setDrawerRef}
        disabled={initializing}
        drawerWidth={300}
        responderNegotiate={responderNegotiate}
        drawerContent={<Sidebar drawer={{}} />}
      >
        <StatusBar backgroundColor='transparent' translucent barStyle='light-content' />
        <Switch>
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

export const App = connect(mapStateToProps, mapDispatchToProps)(_App);