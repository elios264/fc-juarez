FC JUAREZ
=======================
Cliente Android & IOS

- Descargar banners pub
- pantallas de no encontrado
- mejorar seleccion de torneo?
- push notifications
- arreglar iconos minuto a minuto
- ubicacion de equipos...


Datos necesarios:

- Banner mas reciente /api.php/Banner?order=InputDate,desc&page=1,1&columns=BannerId
- Temporadas (Season)
- Torneos (Tournament)
- Partidos (GameFuture) /api.php/GameFuture?columns=GameFutureId,SeasonId,TournamentId,Date,Hour,VersusTeam,VersusTeamAtHome,Stadium&filter[]=Active,eq,1&filter[]=Date,ge,2017-06-16
- Resultados (GamePresent)
    /api.php/GamePresent?columns=GamePresentId,GameFutureId,ScoreHome,ScoreAway
    /api.php/GamePresent?columns=GamePresentId,GameFutureId,ScoreHome,ScoreAway&filter=GameFutureId,in,1,2,3
- Proximo Partido //De los partidos futuros obtener el mas reciente de la fecha actual para adelante,
    /api.php/GameFuture/{id}
- Minuto a minuto (GamePresentMinute) //De los partidos presentes obtener el mas reciente de la fecha actual o anterior
    /api.php/GamePresentMinute?columns=GamePresentId,GameEventId,Minute,Description&filter=GamePresentId,eq,{GamePresentId}
- Tabla general
    http://administrador.ligamx.net/webservices/prtl_web_jsondata.ashx?psWidget=PRTL_EstdClubDtll&objIDDivision=2&objIDTemporada=68&objIDTorneo=1


  Descargar:
    Url de banner mas reciente,
    Lista de temporadas,
    Lista de torneos,
    Lista de Partidos,
    Lista de resultados,
    Detalles de proximo partido,
    Informacion de minuto a minuto,
    Informacion de tabla general.