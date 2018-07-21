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
import { CacheableImage } from 'fc_juarez/src/utils';

const mapStateToProps = (state) => ({
  welcomeBannerUrl: state.objects.welcomeBannerUrl,
  ad: state.objects.ads[Advertisement.BigAd],
  refreshing: state.refreshing
});
const mapDispatchToProps = (dispatch) => bindActionCreators({ loadFromServer }, dispatch);
@connect(mapStateToProps, mapDispatchToProps)
@NativeTachyons.wrap
export class Welcome extends PureComponent {

  static propTypes = {
    loadFromServer: PropTypes.func.isRequired,
    welcomeBannerUrl: PropTypes.string,
    ad: PropTypes.instanceOf(Advertisement),
    refreshing: PropTypes.bool.isRequired,
  }

  render() {

    const { welcomeBannerUrl, ad, refreshing, loadFromServer } = this.props;

    return (
      <View cls='bg-white flx-i'>
        <View cls='flx-i'>
          <CacheableImage cls='absolute-fill rm-cover bg-black' style={[styles.expand]} source={welcomeBannerUrl ? { uri: welcomeBannerUrl } : require('fc_juarez/assets/img/welcomebg.png')} />
          <ScrollView cls='flx-i' contentContainerStyle={styles.scrollContent} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={loadFromServer} tintColor='white' />} >
            <View cls='mh4' >
              {!welcomeBannerUrl && <Image cls='rm-contain' style={[styles.expandHor]} source={require('fc_juarez/assets/img/welcomebg2.png')} />}
            </View>
          </ScrollView>
          <ScalableImage cls='absolute bottom-0 left-0' width={Dimensions.get('window').width} source={require('fc_juarez/assets/img/green-bar.png')} />
        </View>
        <View cls='h4 pa2'>
          <TouchableHighlight onPress={ad ? ad.openTarget : _.noop} >
            <CacheableImage style={[styles.expand]} source={ ad ? { uri: ad.url } : require('fc_juarez/assets/img/ads/bigAd.png')} />
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