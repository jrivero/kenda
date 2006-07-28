<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es-ES" lang="es-ES">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>{$APP_TITULO}: {$MOD_TITULO}</title>
  <link rel="stylesheet" type="text/css" media="screen" title="basico" href="{$THEMECSS}" />
  <script src="thm/js/mixmaxie.js" type="text/javascript"></script>
  <script src="thm/js/prototype.js" type="text/javascript"></script>
  <script src="thm/js/effects.js" type="text/javascript"></script>
  <script src="thm/js/validation.js" type="text/javascript"></script>
</head>
<body>
<div id="wrapper">
  <div id="header">
    <h1>{$APP_TITULO}</h1>
    <ul>
      {section name=i loop=$MODULE_LIST}
      <li><a href="?mod={$MODULE_LIST[i]}">{$MODULE_LIST[i]|capitalize}</a></li>
      {/section}  
    </ul>
  </div>
  <div class="ow"><div class="iw">
    <div id="page">
      <div class="breadcumb"><a href="./" title="{$APP_TITULO}: {$MOD_DEFAULT}">Inicio</a> &raquo; <strong>{$MOD_TITULO}</strong> &raquo; <i>{$MOD_METHOD}</i></div>
        <div id="content">
          {$CONTENIDO}
          {$KENDA_RPT}
        </div>
      <div id="footer"><p>{$APP_TITULO} with KeNDA v{$KENDA_VERSION} - {$APP_CREDITOS}</p></div>
    </div>
  </div></div>
</div>
</body>
</html>
<!-- Kenda Framework : (tiS) http://www.tiservinet.es -->