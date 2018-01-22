import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, StatusBar } from 'react-native';
import { connect } from 'react-redux';
import Drawer from 'react-native-drawer-menu';
import { Route, Switch } from 'react-router-native';
import OneSignal from 'react-native-onesignal';
import _ from 'lodash';

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
    OneSignal.addEventListener('opened', this.onOpened);
  }

  componentWillUnmount() {
    OneSignal.removeEventListener('opened', this.onOpened);
  }

  onOpened = (openResult) => {
    const pageToNavigate = _.get(openResult, 'notification.payload.additionalData.page');
    switch (pageToNavigate) {
      case 'minute': this.props.history.push('/the-minute'); break;
      case 'matchCalendar': this.props.history.push('/next-match'); break;
      default: break;
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