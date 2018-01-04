import { Dimensions, Platform } from 'react-native';

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