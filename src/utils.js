import _ from 'lodash';
import { Dimensions, Platform } from 'react-native';
import { YouTubeStandaloneAndroid, YouTubeStandaloneIOS } from 'react-native-youtube';
import Orientation from 'react-native-orientation';

import { youtubeApiKey } from 'fcjuarez/app.json';


const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

export const debounceCall = (method, delay = 250) => {
  let timeout = null;
  let items = [];
  return (arg1) => {
    items.push(arg1);
    clearTimeout(timeout);
    timeout = setTimeout(() => { method(items); items = []; }, delay);
  };
};
export const isIphoneX = () => {
  const { height, width } = Dimensions.get('window');

  return (
    // This has to be iOS duh
    Platform.OS === 'ios' &&

    // Accounting for the height in either orientation
    (height === 812 || width === 812)
  );
};
export const ifIphoneX = (iphoneXStyle, regularStyle) => {
  if (isIphoneX()) {
    return iphoneXStyle;
  } else {
    return regularStyle;
  }
};
export const btoa = (input = '') => {
  const str = input;
  let output = '';

  for (let block = 0, charCode, i = 0, map = chars;
    str.charAt(i | 0) || (map = '=', i % 1);
    output += map.charAt(63 & block >> 8 - i % 1 * 8)) {

    charCode = str.charCodeAt(i += 3 / 4);

    if (charCode > 0xFF) {
      throw new Error('btoa failed: The string to be encoded contains characters outside of the Latin1 range.');
    }

    block = block << 8 | charCode;
  }

  return output;
};
export const utf8ArrayToStr = (array) => {
  let out, i, c;
  let char2, char3;

  out = '';
  const len = array.length;
  i = 0;
  while (i < len) {
    c = array[i++];
    switch (c >> 4) {
      case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
      // 0xxxxxxx
        out += String.fromCharCode(c);
        break;
      case 12: case 13:
      // 110x xxxx   10xx xxxx
        char2 = array[i++];
        out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
        break;
      case 14:
      // 1110 xxxx  10xx xxxx  10xx xxxx
        char2 = array[i++];
        char3 = array[i++];
        out += String.fromCharCode(((c & 0x0F) << 12) |
                     ((char2 & 0x3F) << 6) |
                     ((char3 & 0x3F) << 0));
        break;
      default: break;
    }
  }

  return out;
};
export const br2nl = (str) => {
  return str.replace(/<br\s*\/?>/mg, '\n');
};
export const getValue = (value, mapping = {}, defaultValue) => _.get(mapping, `[${value}]`, defaultValue);

export const playYoutubeVideo = async (videoId) => {
  let result;
  Orientation.unlockAllOrientations();

  if (Platform.OS === 'android')
    result = await YouTubeStandaloneAndroid.playVideo({ videoId, autoplay: true, startTime: 0, apiKey: youtubeApiKey });
  else if (Platform.OS === 'ios' )
    result = await YouTubeStandaloneIOS.playVideo(videoId);

  Orientation.lockToPortrait();
  return result;
};

export const getYoutubeVideoIdFromUrl = (url) => {
  var regExp = /^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
  var match = _.defaultTo(url, '').match(regExp);
  if (match && match[2].length == 11)
    return match[2];
  else
    return undefined;
};