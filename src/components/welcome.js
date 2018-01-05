import React, { PureComponent } from 'react';
import { StyleSheet, View, Image, Dimensions, ScrollView, RefreshControl } from 'react-native';
import NativeTachyons from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';


@NativeTachyons.wrap
export class Welcome extends PureComponent {

  state = { refreshing: false };

  onRefresh = async () => {
    this.setState({ refreshing: true });
    await new Promise((res) => setTimeout(res, 2000));
    this.setState({ refreshing: false });
  }

  render() {

    return (
      <View cls='bg-white flx-i'>
        <View cls='flx-i'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={require('fc_juarez/assets/img/temp/welcomebg.png')} />
          <ScrollView cls='flx-i' contentContainerStyle={styles.scrollContent} refreshControl={<RefreshControl refreshing={this.state.refreshing} onRefresh={this.onRefresh} tintColor='white' />} >
            <View cls='mh4' >
              <Image cls='rm-contain' style={[styles.expandHor]} source={require('fc_juarez/assets/img/temp/welcomebg2.png')} />
            </View>
          </ScrollView>
          <ScalableImage cls='absolute bottom-0 left-0' width={Dimensions.get('window').width} source={require('fc_juarez/assets/img/green-bar.png')} />
        </View>
        <View cls='h4 pa2'>
          <Image style={[styles.expand]} source={require('fc_juarez/assets/img/temp/welcomead.png')} />
        </View>
      </View>
    );
  }
}

const styles = StyleSheet.create({
  scrollContent: {
    flex: 1,
    justifyContent: 'center'
  },
  expand: {
    width: '100%',
    height: '100%'
  },
  expandHor: {
    width: '100%',
  }
});