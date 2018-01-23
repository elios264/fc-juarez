import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { StyleSheet, View, Image, Dimensions, ScrollView, RefreshControl, TouchableHighlight } from 'react-native';
import { bindActionCreators } from 'redux';
import { connect } from 'react-redux';
import _ from 'lodash';
import NativeTachyons from 'react-native-style-tachyons';
import ScalableImage from 'react-native-scalable-image';
import { loadFromServer } from 'fc_juarez/src/actions/initializers';
import { Advertisement } from 'fc_juarez/src/objects';

const mapStateToProps = (state) => ({ welcomeBannerUrl: state.objects.welcomeBannerUrl, ad: state.objects.ads[Advertisement.BigAd] });
const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
@connect(mapStateToProps, mapDispatchToProps)
@NativeTachyons.wrap
export class Welcome extends PureComponent {

  static propTypes = {
    loadFromServer: PropTypes.func.isRequired,
    welcomeBannerUrl: PropTypes.string,
    ad: PropTypes.instanceOf(Advertisement)
  }

  state = { refreshing: false };

  onRefresh = async () => {
    this.setState({ refreshing: true });
    await this.props.loadFromServer();
    this.setState({ refreshing: false });
  }

  render() {

    const { welcomeBannerUrl, ad } = this.props;

    return (
      <View cls='bg-white flx-i'>
        <View cls='flx-i'>
          <Image cls='absolute-fill rm-cover' style={[styles.expand]} source={welcomeBannerUrl ? { uri: welcomeBannerUrl } : require('fc_juarez/assets/img/welcomebg.png')} />
          <ScrollView cls='flx-i' contentContainerStyle={styles.scrollContent} refreshControl={<RefreshControl refreshing={this.state.refreshing} onRefresh={this.onRefresh} tintColor='white' />} >
            <View cls='mh4' >
              {!welcomeBannerUrl && <Image cls='rm-contain' style={[styles.expandHor]} source={require('fc_juarez/assets/img/welcomebg2.png')} />}
            </View>
          </ScrollView>
          <ScalableImage cls='absolute bottom-0 left-0' width={Dimensions.get('window').width} source={require('fc_juarez/assets/img/green-bar.png')} />
        </View>
        <View cls='h4 pa2'>
          <TouchableHighlight onPress={ad ? ad.openTarget : _.noop} >
            <Image style={[styles.expand]} source={ ad ? { uri: ad.url } : require('fc_juarez/assets/img/ads/bigAd.png')} />
          </TouchableHighlight>
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