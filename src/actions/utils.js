import _ from 'lodash';
import { debounceCall } from '../utils';
import { Alert } from 'react-native';

export const displayMessage = (title, message) => {
  Alert.alert(title, message);
};
export const displayError = debounceCall((errors) => {
  setTimeout(() => {
    errors = Array.from(new Set(errors));
    const many = errors.length > 1;
    const messages = errors.reverse().map((err, i) => many && i === 0 ? `${err}:` : err).join('\n');
    Alert.alert('¡Ups!', messages);
  }, 500);
});
export const confirmAction = (title, message) => {
  return new Promise((res) => {
    Alert.alert(title, message, [
      { text: 'Cancelar', onPress: () => res(false), style: 'cancel' },
      { text: 'Continuar', onPress: () => res(true) }
    ], { cancelable: false });
  });
};
export const displaySuccess = (message) => Alert.alert('Exito', message);
export const catchError = (fn, errorMessage, returnOnFail) => async (...args) => {
  try {
    return await fn(...args);
  } catch (error) {
    console.warn(error.message);
    if (errorMessage) displayError(errorMessage);
    return _.isFunction(returnOnFail) ? returnOnFail(...args) : returnOnFail;
  }
};