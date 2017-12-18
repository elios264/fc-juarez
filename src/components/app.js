import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, StatusBar } from 'react-native';
import { connect } from 'react-redux';
import Drawer from 'react-native-drawer-menu';
import { Route, Switch } from 'react-router-native';

import { Sidebar } from './sideBar';
import { Header } from './header';
import { Loader } from './loader';
import { Welcome } from './welcome';
import { Settings } from './settings';

const mapStateToProps = (state) => ({ initializing: state.initializing });

@connect(mapStateToProps)
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
        <StatusBar backgroundColor='transparent' translucent barStyle='light-content' />
        <Switch>
          { initializing && <Route component={Loader} /> }
          <Route render={() => (
            <View style={styles.flex}>
              <Header drawer={this.drawer} />
              <Switch>
                <Route exact path='/' component={Welcome} />
                <Route exact path='/settings' component={Settings} />
              </Switch>
            </View>
          )} />
        </Switch>



      </Drawer>
    );
  }
}

const styles = StyleSheet.create({
  flex: { flex: 1 }
});