<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: categories.tpl.php,v 1.15 2014-01-09 13:30:47 mbertin Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

// template for PMB OPAC

// �l�ments pour la recherche simple

// tpl_div_categories : le bloc qui contient toutes les cat�gories, pr�sent�es correctement
//   !!root_categories!! : sera remplac� par autant de blocs $tpl_div_category qu'ils y a
//                         de cat�gories de niveau 0.
$tpl_div_categories = "
<div id='categories'>
<h3><span id='titre_categories'>$msg[categories]</span></h3>
<!-- liens_thesaurus -->
<div id='categories-container'>
!!root_categories!!
</div>
<div style='clear: both; visibility: hidden; display: none;' id='category_bloc_sep'>&nbsp;</div>
</div>
";

// tpl_div_category : le bloc qui contient une cat�gorie et ses fils de premier niveau.
//   !!categoryname!! : sera remplac� par le nom de la cat�gorie de niveau 0
//   !!subcategories!! : sera rempalc� par autant de blocs $tpl_subcategory qu'il y a
//                       de fils de premiers niveau
$tpl_div_category =
"<div class='category' >
<h2><img src='./images/folder.gif'>!!category_name!!</h2>
<ul>
!!sub_categories!!
<div class='clear'></div>
</ul>
</div>
";

// tpl_subcategory : le petit bloc qui contient le fils de premier niveau d'une cat�gorie
//   !!subcategory!! : sera remplac� par le nom du fils de premier niveau de la cat�gorie.
$tpl_subcategory =
"<li>!!sub_category!!</li>
";

?>
