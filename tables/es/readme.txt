------------------------------------------------------------------------------------------------------------------

Descripci�n de los archivos
bibli.sql : estructura de la base de datos sola, sin datos

minimum.sql : usuario admin/admin, par�metros de la aplicaci�n

feed_essential.sql : lo que se necesita para inciiar la aplicaci�n en modo inicio r�pido :
	Datos de la aplicaci�n rellenados, modificables.
	Un juego de copia de seguridad listo a emplear
	Un juego de par�metros de Z3950.
	
data_test.sql : una peque�a selecci�n de datos de registros, usuarios, para poder probar PMB.
	Registros, usuarios, pr�stamos, ejemplares, publicaciones peri�dicas
	Se basa en los datos de la aplicaci�n inclu�dos tambi�n en feed_essential.sql
	Debe cargarse el tesauro UNESCO_FR unesco_fr.sql
	
Tesauros : se proponen 3 tesauros :
	unesco_fr.sql : tesauro jer�rquico de la UNESCO, bastante importante y bien hecho.
	grumeau.sql : un poco m�s peque�o, m�s sencillo pero bien constru�do tambi�n.
	environnement : un tesauro �til para un fondo documental relacionado con el Medio ambiente.
	
Indexations internes : se proponen 4 indexaciones :
	indexint_100.sql : 100 casos del saber o margarita de colores, indexaci�n decimal 
	style Dewey simplificada para educaci�n
	indexint_chambery.sql : indexaci�n de estilo Dewey de la BM de Chamb�ry, bien concebido pero poco adaptada
	a bibliotecas peque�as
	indexint_dewey.sql : indexaci�n estilo Dewey
	indexint_small_en.sql : indexaci�n estilo Dewey reducida y en ingl�s
	

************************************************************************************************
________________________________________________________________________________________________
Atenci�n, si est�s haciendo una actualizaci�n de una versi�n anterior :
------------------------------------------------------------------------------------------------
*********** A realizar con cada instalaci�n o actualizaci�n de la aplicaci�n  ****************
Cuando instalas una nueva versi�n
sobre una versi�n anterior debes, obligatoriamente,
antes de copiar los arhivos nuevos contenidos en el archivo zip
al servidor web :

comprobar que los par�metros incluidos en :
./includes/db_param.inc.php
./opac_css/includes/opac_db_param.inc.php

corresponden a tu configuraci�n (haz una copia antes !)

Adem�s :
Debes hacer una actualizaci�n de la base de datos.
No se perder� nada.

Con�ctate de forma habitual a PMB, el estilo gr�fico puede ser diferente, 
ausente (visualizaci�n sin colores ni im�gines)

Ve a Administraci�n > Herramientas > act base de datos para actualizar la
base de datos.

Una serie de mensajes te ir�n indicando las actualizaciones sucesivas, 
para continuar la actualizaci�n haz clic en el enlace de la parte inferior de la p�gina 
hasta que aparezca el mensaje 'Tu base de datos est� al d�a con la versi�n...'

Puedes editar tu cuenta de usuario para modificar tus preferencias, cambiando
el estilo de visualizaci�n.

No dudes en hacernos llegar tus dudas, problemas o sugerencias por correo
electr�nico : pmb@sigb.net

Por otro lado, estaremos encantados de contar contigo como uno de nuestros
usuarios, y si nos facilitaras algunos datos como n�mero de usuarios, de obras
de CD... junto con los datos de tu establecimiento (o a t�tulo particular) 
nos ayudar�s a conocerte mejor.

Encontrar�s m�s informaci�n en el directorio ./doc o bien 
en nuestra p�gina web http://www.sigb.net

El equipo de desarrolladores.


///////////////////// Lista de tablas incluidas en los archivos /////////////////

\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            minimum.sql
# Contenido de la tabla abts_periodicites
# Contenido de la tabla categories
# Contenido de la tabla classements
# Contenido de la tabla empr_statut
# Contenido de la tabla infopages
# Contenido de la tabla lenders
# Contenido de la tabla lignes_actes_statuts
# Contenido de la tabla noeuds
# Contenido de la tabla notice_statut
# Contenido de la tabla origin_authorities
# Contenido de la tabla origine_notice
# Contenido de la tabla parametres
# Contenido de la tabla pclassement
# Contenido de la tabla sauv_sauvegardes
# Contenido de la tabla sauv_tables
# Contenido de la tabla suggestions_categ
# Contenido de la tabla thesaurus
# Contenido de la tabla users
	usuario admin/admin
# Contenido de la tabla z_attr
# Contenido de la tabla z_bib

\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            feed_essential.sql
# Contenido de la tabla arch_emplacement
# Contenido de la tabla arch_statut
# Contenido de la tabla arch_type
# Contenido de la tabla caddie
# Contenido de la tabla caddie_procs
# Contenido de la tabla docs_codestat
# Contenido de la tabla docs_location
# Contenido de la tabla docs_section
# Contenido de la tabla docs_statut
# Contenido de la tabla docs_type
# Contenido de la tabla docsloc_section
# Contenido de la tabla empr_caddie
# Contenido de la tabla empr_caddie_procs
# Contenido de la tabla empr_categ
# Contenido de la tabla empr_codestat
# Contenido de la tabla etagere
# Contenido de la tabla etagere_caddie
# Contenido de la tabla expl_custom
# Contenido de la tabla expl_custom_lists
# Contenido de la tabla facettes
# Contenido de la tabla notice_tpl
# Contenido de la tabla notice_tplcode
# Contenido de la tabla procs
# Contenido de la tabla procs_classements
# Contenido de la tabla search_perso
# Contenido de la tabla statopac_request
# Contenido de la tabla statopac_vue_1
# Contenido de la tabla statopac_vue_2
# Contenido de la tabla statopac_vue_3
# Contenido de la tabla statopac_vue_4
# Contenido de la tabla statopac_vue_5
# Contenido de la tabla statopac_vues
# Contenido de la tabla statopac_vues_col

\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            bibliportail.sql
# Contenu n�c�ssaire � la demo du portail


\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            data_test.sql
# Contenido de la tabla analysis
# Contenido de la tabla authors
# Contenido de la tabla bannette_abon
# Contenido de la tabla bannette_contenu
# Contenido de la tabla bannette_equation
# Contenido de la tabla bannettes
# Contenido de la tabla bulletins
# Contenido de la tabla caddie_content
# Contenido de la tabla collections
# Contenido de la tabla connectors_categ
# Contenido de la tabla connectors_categ_sources
# Contenido de la tabla connectors_sources
# Contenido de la tabla empr
# Contenido de la tabla entrepot_source_2
# Contenido de la tabla entrepot_source_4
# Contenido de la tabla entrepot_source_5
# Contenido de la tabla equations
# Contenido de la tabla exemplaires
# Contenido de la tabla explnum
# Contenido de la tabla external_count
# Contenido de la tabla notices
# Contenido de la tabla notices_categories
# Contenido de la tabla notices_fields_global_index
# Contenido de la tabla notices_global_index
# Contenido de la tabla notices_langues
# Contenido de la tabla notices_mots_global_index
# Contenido de la tabla notices_relations
# Contenido de la tabla publishers
# Contenido de la tabla responsability
# Contenido de la tabla series
# Contenido de la tabla sources_enrichment
# Contenido de la tabla words


\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            agneaux.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            unesco.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            environnement.sql
# Contenido de la tabla voir_aussi
# Contenido de la tabla categories
# Contenido de la tabla noeuds
# Contenido de la tabla thesaurus

\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            indexint_chambery.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            indexint_100.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            indexint_dewey.sql
\_/-\_/-\_/-\_/-\_/-\_/-\_/-\            indexint_small_en.sql
# Contenido de la tabla indexint
# Contenido de la tabla pclassement