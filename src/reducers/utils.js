import _ from 'lodash';

export const createCRUDObjectReducer = (prefix, key = 'id') => {
  return (state = {}, action) => {
    switch (action.type) {
      case `${prefix}_FETCHED`: return _.keyBy(action.objects, key);
      case `${prefix}_ADDED`:
      case `${prefix}_UPDATED`: return { ...state, ..._.keyBy(action.objects, key) };
      case `${prefix}_REMOVED`: return _(action.objects).map(key).reduce((acc, cur) => (delete acc[cur], acc), { ...state });
      default: return state;
    }
  };
};
