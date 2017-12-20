import { catchError } from './utils';
import { appStart } from './appState';


export const intialize = () => catchError(async(dispatch) => {
  dispatch({ type: 'INITIALIZING', running: true });

  await dispatch(appStart());
  //await ServiceApi.initialize(dispatch);
  //await ServiceApi.downloadAllData();
  await new Promise((res) => setTimeout(res, 5000));

  dispatch({ type: 'INITIALIZING', running: false });
}, 'Ha ocurrido un error inicializando el sistema');