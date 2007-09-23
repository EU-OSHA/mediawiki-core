<?php
/**
 * Tetun
 *
 * @addtogroup Language
 *
 * @author MF-Warburg
 */

$namespaceNames = array(
	NS_MEDIA            => 'Media',
	NS_SPECIAL          => 'Espesiál',
	NS_MAIN             => '',
	NS_TALK             => 'Diskusaun',
	NS_USER             => "Uza-na'in",
	NS_USER_TALK        => "Diskusaun_Uza-na'in",
	# NS_PROJECT set by $wgMetaNamespace
	NS_PROJECT_TALK     => 'Diskusaun_$1',
	NS_IMAGE            => 'Imajen',
	NS_IMAGE_TALK       => 'Diskusaun_Imajen',
	NS_MEDIAWIKI        => 'MediaWiki',
	NS_MEDIAWIKI_TALK   => 'Diskusaun_MediaWiki',
	NS_TEMPLATE         => 'Template',
	NS_TEMPLATE_TALK    => 'Diskusaun_Template',
	NS_HELP             => 'Ajuda',
	NS_HELP_TALK        => 'Diskusaun_Ajuda',
	NS_CATEGORY         => 'Kategoría',
	NS_CATEGORY_TALK    => 'Diskusaun_Kategoría'
);

$messages = array(
# User preference toggles
'tog-hideminor' => "Lá'os hatudu osan-rahun sira iha mudansa foufoun sira",

'underline-always' => 'Sempre',
'underline-never'  => 'Nunka',

# Dates
'sunday'        => 'Loron-domingu',
'monday'        => 'Loron-segunda',
'tuesday'       => 'Loron-tersa',
'wednesday'     => 'Loron-kuarta',
'thursday'      => 'Loron-kinta',
'friday'        => 'Loron-sesta',
'saturday'      => 'Loron-sábadu',
'january'       => 'Janeiru',
'february'      => 'Fevereiru',
'march'         => 'Marsu',
'april'         => 'Abríl',
'may_long'      => 'Maiu',
'june'          => 'Juñu',
'july'          => 'Jullu',
'august'        => 'Agostu',
'september'     => 'Setembru',
'october'       => 'Outubru',
'november'      => 'Novembru',
'december'      => 'Dezembru',
'january-gen'   => 'Janeiru nian',
'february-gen'  => 'Fevereiru nian',
'march-gen'     => 'Marsu nian',
'april-gen'     => 'Abríl nian',
'may-gen'       => 'Maiu nian',
'june-gen'      => 'Juñu nian',
'july-gen'      => 'Jullu nian',
'august-gen'    => 'Agostu nian',
'september-gen' => 'Setembru nian',
'october-gen'   => 'Outubru nian',
'november-gen'  => 'Novembru nian',
'december-gen'  => 'Dezembru nian',

# Bits of text used by many pages
'categories'      => 'Kategoría',
'pagecategories'  => '{{PLURAL:$1|Kategoría|Kategoría}}',
'category_header' => 'Artigu iha kategoría "$1"',
'category-empty'  => "''Iha kategoría ne'e agora pájina lá'os.''",

'about'          => 'Kona-ba',
'qbfind'         => 'Hetan',
'qbedit'         => 'Edita',
'qbpageoptions'  => "Pájina ne'e",
'qbmyoptions'    => "Ha'u-nia pájina sira",
'qbspecialpages' => 'Pájina espesiál sira',
'moredotdotdot'  => 'Barak liu...',
'mypage'         => "Ha'u-nia pájina",
'mytalk'         => "Ha'u-nia diskusaun",

'errorpagetitle'   => 'Sala',
'help'             => 'Ajuda',
'search'           => 'Buka',
'searchbutton'     => 'Buka',
'history'          => 'Istória pájina',
'history_short'    => 'Istória',
'info_short'       => 'Informasaun',
'printableversion' => 'Versaun ba impresaun',
'permalink'        => 'Ligasaun mahelak',
'print'            => 'Imprime',
'edit'             => 'Edita',
'editthispage'     => "Edita pájina ne'e",
'delete'           => 'Halakon',
'deletethispage'   => "Halakon pájina ne'e",
'protect'          => 'Proteje',
'newpage'          => 'Pájina foun',
'talkpagelinktext' => 'Diskusaun',
'specialpage'      => 'Pájina espesiál',
'talk'             => 'Diskusaun',
'otherlanguages'   => 'Iha lian seluk',

# All link text and link target definitions of links into project namespace that get used by other message strings, with the exception of user group pages (see grouppage) and the disambiguation template definition (see disambiguations).
'aboutsite' => 'Kona-ba {{SITENAME}}',
'aboutpage' => 'Project:Kona-ba',
'mainpage'  => 'Pájina Mahuluk',

'versionrequired'     => 'Presiza MediaWiki versaun $1',
'versionrequiredtext' => "Presiza MediaWiki versaun $1 ba uza pájina ne'e. Haree [[Special:Version|pájina versaun]].",

'youhavenewmessages' => 'Ó iha $1 ($2).',
'newmessageslink'    => 'mensajen foun',
'editsection'        => 'edita',
'editold'            => 'edita',
'showtoc'            => 'hatudu',
'hidetoc'            => 'subar',

# Short words for each namespace, by default used in the 'article' tab in monobook
'nstab-main'      => 'Artigu',
'nstab-user'      => "Pájina uza-na'in",
'nstab-special'   => 'Espesiál',
'nstab-project'   => 'Pájina projetu nian',
'nstab-mediawiki' => 'Mensajen',
'nstab-help'      => 'Pájina ajuda',
'nstab-category'  => 'Kategoría',

# General errors
'error' => 'Sala',

# Edit pages
'minoredit' => "Ne'e osan-rahun",
'watchthis' => "Hateke pájina ne'e",

# Preferences page
'prefs-watchlist' => 'Lista hateke',

# Groups
'group'            => 'Lubu:',
'group-bot'        => 'Bot sira',
'group-sysop'      => 'Administradór sira',
'group-bureaucrat' => 'Burokrata sira',
'group-all'        => '(hotu)',

'group-sysop-member'      => 'Administradór',
'group-bureaucrat-member' => 'Burokrata',

'grouppage-bot'        => '{{ns:project}}:Bot sira',
'grouppage-sysop'      => '{{ns:project}}:Administradór sira',
'grouppage-bureaucrat' => '{{ns:project}}:Burokrata sira',

# User rights log
'rightsnone' => '(mamuk)',

# Recent changes
'recentchanges'   => 'Mudansa foufoun sira',
'hide'            => 'Hamsumik',
'show'            => 'Hatudu',
'minoreditletter' => 'o',
'newpageletter'   => 'F',

# Upload
'watchthisupload' => "Hateke pájina ne'e",

# Image list
'ilsubmit'       => 'Buka',
'imagelist_name' => 'Naran',
'imagelist_user' => "Uza-na'in",

# File reversion
'filerevert-comment' => 'Komentáriu:',

# File deletion
'filedelete' => 'Halakon $1',

# Miscellaneous special pages
'allpages'     => 'Pájina hotu',
'move'         => 'Book',
'movethispage' => "Book pájina ne'e",

# Watchlist
'watchlist'     => "Ha'u-nia lista hateke",
'mywatchlist'   => "Ha'u-nia lista hateke",
'watch'         => 'Hateke',
'watchthispage' => "Hateke pájina ne'e",
'unwatch'       => 'La hateke',

# 'all' in various places, this might be different for inflected languages
'recentchangesall' => 'hotu',
'imagelistall'     => 'hotu',
'watchlistall2'    => 'hotu',
'namespacesall'    => 'hotu',
'monthsall'        => 'hotu',

);
