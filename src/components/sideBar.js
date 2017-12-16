import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Text, View } from 'react-native';
import NativeTachyons from 'react-native-style-tachyons';

@NativeTachyons.wrap
export class Sidebar extends PureComponent {

  static propTypes = {
    drawer: PropTypes.object.isRequired,
  }

  render() {
    //const { drawer } = this.props;

    return (
      <View cls='flx-i jcc aic bg-primarydark'>
        <Text cls='contrast f4 tc white'>
          Menu here on own file!
        </Text>
      </View>
    );
  }
}