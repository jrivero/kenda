<h1>{$MOD_TITULO}</h1>
<h2>{$MOD_DESCRIPCION}</h2>

<ul>
  {section name=i loop=$POST}
  <li><a href="?mod=page&exec=ver&id={$POST[i].post_id}">{$POST[i].post_titulo}</a></li>
  {/section}
</ul>